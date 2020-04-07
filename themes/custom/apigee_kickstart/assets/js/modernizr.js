/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/modernizr.js":
/*!*****************************!*\
  !*** ./src/js/modernizr.js ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/*! modernizr 3.6.0 (Custom Build) | MIT *
 * https://modernizr.com/download/?-cssgrid_cssgridlegacy-setclasses !*/
!function (e, n, t) {
  function r(e, n) {
    return _typeof(e) === n;
  }

  function o() {
    var e, n, t, o, s, i, l;

    for (var a in w) {
      if (w.hasOwnProperty(a)) {
        if (e = [], n = w[a], n.name && (e.push(n.name.toLowerCase()), n.options && n.options.aliases && n.options.aliases.length)) for (t = 0; t < n.options.aliases.length; t++) {
          e.push(n.options.aliases[t].toLowerCase());
        }

        for (o = r(n.fn, "function") ? n.fn() : n.fn, s = 0; s < e.length; s++) {
          i = e[s], l = i.split("."), 1 === l.length ? Modernizr[l[0]] = o : (!Modernizr[l[0]] || Modernizr[l[0]] instanceof Boolean || (Modernizr[l[0]] = new Boolean(Modernizr[l[0]])), Modernizr[l[0]][l[1]] = o), C.push((o ? "" : "no-") + l.join("-"));
        }
      }
    }
  }

  function s(e) {
    var n = _.className,
        t = Modernizr._config.classPrefix || "";

    if (x && (n = n.baseVal), Modernizr._config.enableJSClass) {
      var r = new RegExp("(^|\\s)" + t + "no-js(\\s|$)");
      n = n.replace(r, "$1" + t + "js$2");
    }

    Modernizr._config.enableClasses && (n += " " + t + e.join(" " + t), x ? _.className.baseVal = n : _.className = n);
  }

  function i(e, n) {
    return !!~("" + e).indexOf(n);
  }

  function l() {
    return "function" != typeof n.createElement ? n.createElement(arguments[0]) : x ? n.createElementNS.call(n, "http://www.w3.org/2000/svg", arguments[0]) : n.createElement.apply(n, arguments);
  }

  function a(e) {
    return e.replace(/([a-z])-([a-z])/g, function (e, n, t) {
      return n + t.toUpperCase();
    }).replace(/^-/, "");
  }

  function u(n, t, r) {
    var o;

    if ("getComputedStyle" in e) {
      o = getComputedStyle.call(e, n, t);
      var s = e.console;
      if (null !== o) r && (o = o.getPropertyValue(r));else if (s) {
        var i = s.error ? "error" : "log";
        s[i].call(s, "getComputedStyle returning null, its possible modernizr test results are inaccurate");
      }
    } else o = !t && n.currentStyle && n.currentStyle[r];

    return o;
  }

  function f(e) {
    return e.replace(/([A-Z])/g, function (e, n) {
      return "-" + n.toLowerCase();
    }).replace(/^ms-/, "-ms-");
  }

  function c(e, n) {
    return function () {
      return e.apply(n, arguments);
    };
  }

  function d(e, n, t) {
    var o;

    for (var s in e) {
      if (e[s] in n) return t === !1 ? e[s] : (o = n[e[s]], r(o, "function") ? c(o, t || n) : o);
    }

    return !1;
  }

  function p() {
    var e = n.body;
    return e || (e = l(x ? "svg" : "body"), e.fake = !0), e;
  }

  function m(e, t, r, o) {
    var s,
        i,
        a,
        u,
        f = "modernizr",
        c = l("div"),
        d = p();
    if (parseInt(r, 10)) for (; r--;) {
      a = l("div"), a.id = o ? o[r] : f + (r + 1), c.appendChild(a);
    }
    return s = l("style"), s.type = "text/css", s.id = "s" + f, (d.fake ? d : c).appendChild(s), d.appendChild(c), s.styleSheet ? s.styleSheet.cssText = e : s.appendChild(n.createTextNode(e)), c.id = f, d.fake && (d.style.background = "", d.style.overflow = "hidden", u = _.style.overflow, _.style.overflow = "hidden", _.appendChild(d)), i = t(c, e), d.fake ? (d.parentNode.removeChild(d), _.style.overflow = u, _.offsetHeight) : c.parentNode.removeChild(c), !!i;
  }

  function g(n, r) {
    var o = n.length;

    if ("CSS" in e && "supports" in e.CSS) {
      for (; o--;) {
        if (e.CSS.supports(f(n[o]), r)) return !0;
      }

      return !1;
    }

    if ("CSSSupportsRule" in e) {
      for (var s = []; o--;) {
        s.push("(" + f(n[o]) + ":" + r + ")");
      }

      return s = s.join(" or "), m("@supports (" + s + ") { #modernizr { position: absolute; } }", function (e) {
        return "absolute" == u(e, null, "position");
      });
    }

    return t;
  }

  function y(e, n, o, s) {
    function u() {
      c && (delete N.style, delete N.modElem);
    }

    if (s = r(s, "undefined") ? !1 : s, !r(o, "undefined")) {
      var f = g(e, o);
      if (!r(f, "undefined")) return f;
    }

    for (var c, d, p, m, y, v = ["modernizr", "tspan", "samp"]; !N.style && v.length;) {
      c = !0, N.modElem = l(v.shift()), N.style = N.modElem.style;
    }

    for (p = e.length, d = 0; p > d; d++) {
      if (m = e[d], y = N.style[m], i(m, "-") && (m = a(m)), N.style[m] !== t) {
        if (s || r(o, "undefined")) return u(), "pfx" == n ? m : !0;

        try {
          N.style[m] = o;
        } catch (h) {}

        if (N.style[m] != y) return u(), "pfx" == n ? m : !0;
      }
    }

    return u(), !1;
  }

  function v(e, n, t, o, s) {
    var i = e.charAt(0).toUpperCase() + e.slice(1),
        l = (e + " " + P.join(i + " ") + i).split(" ");
    return r(n, "string") || r(n, "undefined") ? y(l, n, o, s) : (l = (e + " " + z.join(i + " ") + i).split(" "), d(l, n, t));
  }

  function h(e, n, r) {
    return v(e, t, t, n, r);
  }

  var C = [],
      w = [],
      S = {
    _version: "3.6.0",
    _config: {
      classPrefix: "",
      enableClasses: !0,
      enableJSClass: !0,
      usePrefixes: !0
    },
    _q: [],
    on: function on(e, n) {
      var t = this;
      setTimeout(function () {
        n(t[e]);
      }, 0);
    },
    addTest: function addTest(e, n, t) {
      w.push({
        name: e,
        fn: n,
        options: t
      });
    },
    addAsyncTest: function addAsyncTest(e) {
      w.push({
        name: null,
        fn: e
      });
    }
  },
      Modernizr = function Modernizr() {};

  Modernizr.prototype = S, Modernizr = new Modernizr();

  var _ = n.documentElement,
      x = "svg" === _.nodeName.toLowerCase(),
      b = "Moz O ms Webkit",
      P = S._config.usePrefixes ? b.split(" ") : [];

  S._cssomPrefixes = P;
  var z = S._config.usePrefixes ? b.toLowerCase().split(" ") : [];
  S._domPrefixes = z;
  var E = {
    elem: l("modernizr")
  };

  Modernizr._q.push(function () {
    delete E.elem;
  });

  var N = {
    style: E.elem.style
  };
  Modernizr._q.unshift(function () {
    delete N.style;
  }), S.testAllProps = v, S.testAllProps = h, Modernizr.addTest("cssgridlegacy", h("grid-columns", "10px", !0)), Modernizr.addTest("cssgrid", h("grid-template-rows", "none", !0)), o(), s(C), delete S.addTest, delete S.addAsyncTest;

  for (var T = 0; T < Modernizr._q.length; T++) {
    Modernizr._q[T]();
  }

  e.Modernizr = Modernizr;
}(window, document);

/***/ }),

/***/ 3:
/*!***********************************!*\
  !*** multi ./src/js/modernizr.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/arshad/Sites/chapterthree/ks/web/profiles/contrib/apigee_devportal_kickstart/themes/custom/apigee_kickstart/src/js/modernizr.js */"./src/js/modernizr.js");


/***/ })

/******/ });