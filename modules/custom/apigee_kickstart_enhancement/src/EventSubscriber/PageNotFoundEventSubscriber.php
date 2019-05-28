<?php

/**
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\apigee_kickstart_enhancement\EventSubscriber;

use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles not found exceptions for apidoc entities.
 *
 * @package Drupal\apigee_kickstart_enhancement\EventSubscriber
 */
class PageNotFoundEventSubscriber implements EventSubscriberInterface {

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The patch matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * PageNotFoundEventSubscriber constructor.
   *
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The patch matcher service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   */
  public function __construct(PathMatcherInterface $path_matcher, PathValidatorInterface $path_validator) {
    $this->pathValidator = $path_validator;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * Redirects to the apidoc canonical route if we have a not found exception.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The exception event.
   */
  public function onNotFoundException(GetResponseForExceptionEvent $event) {
    $path = NULL;

    // Check if the request uri matches an apidoc canonical route.
    // Also check for apidoc valid path.
    if (!($event->getException() instanceof NotFoundHttpException)
      || !(($uri = $event->getRequest()->getRequestUri())
        && ($this->pathMatcher->matchPath($uri, '/api/*/*'))
        && ([, $prefix, $id] = explode('/', $uri))
        && ($path = "/api/{$id}")
        && $this->pathValidator->isValid($path))
    ) {
      return;
    }

    // Redirect to the apidoc.
    $event->setResponse(new RedirectResponse($path));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::EXCEPTION][] = ['onNotFoundException', 0];
    return $events;
  }

}
