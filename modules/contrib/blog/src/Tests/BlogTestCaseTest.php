<?php

/**
 * @file
 * Contains \Drupal\blog\Tests\BlogTestCaseTest.
 */

namespace Drupal\blog\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test blog functionality.
 * 
 * @group blog
 */
class BlogTestCaseTest extends WebTestBase {
  
  protected $big_user;
  protected $own_user;
  protected $any_user;
  
  protected $profile = 'standard';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('blog');
    
  public static function getInfo() {
    return array(
      'name' => 'Blog functionality',
      'description' => 'Create, view, edit, delete, and change blog entries and verify its consistency in the database.',
      'group' => 'Blog',
    );
  }
  
  protected function setUp() {
    parent::setUp();

    // Create users.
    $this->big_user = $this->drupalCreateUser(array('administer blocks'));
    $this->own_user = $this->drupalCreateUser(array('create blog_post content', 'edit own blog_post content', 'delete own blog_post content'));
    $this->any_user = $this->drupalCreateUser(array('create blog_post content', 'edit any blog_post content', 'delete any blog_post content', 'access administration pages'));
  }

  /**
   * Confirm that the "You are not allowed to post a new blog entry." message
   * shows up if a user submitted blog entries, has been denied that
   * permission, and goes to the blog page.
   */
  function testUnprivilegedUser() {
    // Create a blog node for a user with no blog permissions.
    $this->drupalCreateNode(array('type' => 'blog_post', 'uid' => $this->big_user->id()));

    $this->drupalLogin($this->big_user);

    $this->drupalGet('blog/' . $this->big_user->id());
    $this->assertResponse(200);
    $this->assertTitle(t("@name's blog", array('@name' => $this->big_user->getUsername())) . ' | Drupal', t('Blog title was displayed'));
    $this->assertText(t('You are not allowed to post a new blog entry.'), t('No new entries can be posted without the right permission'));
  }

  /**
   * View the blog of a user with no blog entries as another user.
   */
  function testBlogPageNoEntries() {
    $this->drupalLogin($this->big_user);

    $this->drupalGet('blog/' . $this->own_user->id());
    $this->assertResponse(200);
    $this->assertTitle(t("@name's blog", array('@name' => $this->own_user->getUsername())) . ' | Drupal', t('Blog title was displayed'));
    $this->assertText(t('@author has not created any blog entries.', array('@author' => $this->own_user->getUsername())), t('Users blog displayed with no entries'));
  }

  /**
   * Login users, create blog nodes, and test blog functionality through the admin and user interfaces.
   */
  function testBlog() {
      
    // Create a node so that the block of recent posts will display.
    $node = $this->drupalCreateNode(array('type' => 'blog_post', 'uid' => $this->any_user->id()));
    
    // Login the admin user.
    $this->drupalLogin($this->big_user);
      
    // Place the recent blog posts block.
    $blog_block = $this->drupalPlaceBlock('blog_blockblock-views-block-blog-blog-block');
    //print_r($blog_block->label() . "XXXXXXXXXXXXXXXXX \n");

    // Verify the blog block was displayed.
    $this->drupalGet('<front>');
    $this->assertBlockAppears($blog_block);

    // Do basic tests for each user.
    $this->doBasicTests($this->any_user, TRUE);
    $this->doBasicTests($this->own_user, FALSE);

    // Create another blog node for the any blog user.
    $node = $this->drupalCreateNode(array('type' => 'blog_post', 'uid' => $this->any_user->id()));
    // Verify the own blog user only has access to the blog view node.
    $this->verifyBlogs($this->any_user, $node, FALSE, 403);

    // Create another blog node for the own blog user.
    $node = $this->drupalCreateNode(array('type' => 'blog_post', 'uid' => $this->own_user->id()));
    // Login the any blog user.
    $this->drupalLogin($this->any_user);
    // Verify the any blog user has access to all the blog nodes.
    $this->verifyBlogs($this->own_user, $node, TRUE);
  }

  /**
   * Run basic tests on the indicated user.
   *
   * @param object $user
   *   The logged in user.
   * @param boolean $admin
   *   User has 'access administration pages' privilege.
   */
  private function doBasicTests($user, $admin) {
    // Login the user.
    $this->drupalLogin($user);
    // Create blog node.
    $node = $this->drupalCreateNode(array('type' => 'blog_post'));
    // Verify the user has access to all the blog nodes.
    $this->verifyBlogs($user, $node, $admin);
    // Create one more node to test the blog page with more than one node
    $this->drupalCreateNode(array('type' => 'blog_post', 'uid' => $user->id()));
    // Verify the blog links are displayed.
    $this->verifyBlogLinks($user);
  }

  /**
   * Verify the logged in user has the desired access to the various blog nodes.
   *
   * @param object $node_user
   *   The user who creates the node.
   * @param object $node
   *   A node object.
   * @param boolean $admin
   *   User has 'access administration pages' privilege.
   * @param integer $response
   *   HTTP response code.
   */
  private function verifyBlogs($node_user, $node, $admin, $response = 200) {
    $response2 = ($admin) ? 200 : 403;

    // View blog help node.
    $this->drupalGet('admin/help/blog');
    $this->assertResponse($response2);
    if ($response2 == 200) {
      $this->assertTitle(t('Blog | Drupal'), t('Blog help node was displayed'));
      $this->assertText(t('Blog'), t('Blog help node was displayed'));
    }

    // View blog node.
    $this->drupalGet('node/' . $node->id());
    $this->assertResponse(200);
    $this->assertTitle($node->getTitle() . ' | Drupal', t('Blog node was displayed'));
    //$breadcrumb = array(
      //l(t('Home'), NULL),
      //l(t('Blogs'), 'blog'),
      //l(t("!name's blog", array('!name' => $node_user->getUsername())), 'blog/' . $node_user->id()),
    //);

    // @todo sort out the breadcrumbs
    //$this->assertRaw(theme('breadcrumb', array('breadcrumb' => $breadcrumb)), t('Breadcrumbs were displayed'));

    // View blog edit node.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertResponse($response);
    if ($response == 200) {
      $this->assertTitle('Edit Blog post ' . $node->getTitle() . ' | Drupal', t('Blog edit node was displayed'));
    }

    if ($response == 200) {
      // Edit blog node.
      $edit = array();
      $edit["title[0][value]"] = 'node/' . $node->id();
      $edit["body[0][value]"] = $this->randomMachineName(256);
      $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save'));
      $this->assertRaw(t('Blog post %title has been updated.', array('%title' => $edit["title[0][value]"])), t('Blog node was edited'));

      // Delete blog node.
      $this->drupalPostForm('node/' . $node->id() . '/delete', array(), t('Delete'));
      $this->assertResponse($response);
      $this->assertRaw(t('The Blog post %title has been deleted.', array('%title' => $edit["title"])), t('Blog node was deleted'));
    }
  }

  /**
   * Verify the blog links are displayed to the logged in user.
   *
   * @param object $user
   *   The logged in user.
   */
  private function verifyBlogLinks($user) {
    // Confirm blog entries link exists on the user page.
    $this->drupalGet('user/' . $user->id());
    $this->assertResponse(200);
    $this->assertText(t('View recent blog entries'), t('View recent blog entries link was displayed'));

    // Confirm the recent blog entries link goes to the user's blog page.
    $this->clickLink('View recent blog entries');
    $this->assertTitle(t("@name's blog | Drupal", array('@name' => $user->getUsername())), t('View recent blog entries link target was correct'));

    // Confirm a blog page was displayed.
    $this->drupalGet('blog');
    $this->assertResponse(200);
    $this->assertTitle('Blog posts | Drupal', t('Blog page was displayed'));
    $this->assertText(t('Home'), t('Breadcrumbs were displayed'));
    $this->assertLink(t('Create new blog entry'));

    // Confirm a blog page was displayed per user.
    $this->drupalGet('blog/' . $user->id());
    $this->assertTitle(t("@name's blog | Drupal", array('@name' => $user->getUsername())), t('User blog node was displayed'));

    // Confirm a blog feed was displayed.
    $this->drupalGet('blog/feed');
    $this->assertTitle(t('Drupal blog posts'), t('Blog feed was displayed'));

    // Confirm a blog feed was displayed per user.
    $this->drupalGet('blog/' . $user->id() . '/feed');
    $this->assertTitle(t("@name's blog", array('@name' => $user->getUsername())), t('User blog feed was displayed'));
  }
}
