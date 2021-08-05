/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************!*\
  !*** ./src/js/modernizr.js ***!
  \*****************************/
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/*! modernizr 3.6.0 (Custom Build) | MIT *
 * https://modernizr.com/download/?-cssgrid_cssgridlegacy-details-inputtypes-touchevents-addtest-prefixes-setclasses-teststyles !*/
!function (e, t, n) {
  function r(e, t) {
    return _typeof(e) === t;
  }

  function i() {
    var e, t, n, i, o, s, l;

    for (var a in w) {
      if (w.hasOwnProperty(a)) {
        if (e = [], t = w[a], t.name && (e.push(t.name.toLowerCase()), t.options && t.options.aliases && t.options.aliases.length)) for (n = 0; n < t.options.aliases.length; n++) {
          e.push(t.options.aliases[n].toLowerCase());
        }

        for (i = r(t.fn, "function") ? t.fn() : t.fn, o = 0; o < e.length; o++) {
          s = e[o], l = s.split("."), 1 === l.length ? Modernizr[l[0]] = i : (!Modernizr[l[0]] || Modernizr[l[0]] instanceof Boolean || (Modernizr[l[0]] = new Boolean(Modernizr[l[0]])), Modernizr[l[0]][l[1]] = i), _.push((i ? "" : "no-") + l.join("-"));
        }
      }
    }
  }

  function o(e) {
    var t = T.className,
        n = Modernizr._config.classPrefix || "";

    if (z && (t = t.baseVal), Modernizr._config.enableJSClass) {
      var r = new RegExp("(^|\\s)" + n + "no-js(\\s|$)");
      t = t.replace(r, "$1" + n + "js$2");
    }

    Modernizr._config.enableClasses && (t += " " + n + e.join(" " + n), z ? T.className.baseVal = t : T.className = t);
  }

  function s(e, t) {
    if ("object" == _typeof(e)) for (var n in e) {
      x(e, n) && s(n, e[n]);
    } else {
      e = e.toLowerCase();
      var r = e.split("."),
          i = Modernizr[r[0]];
      if (2 == r.length && (i = i[r[1]]), "undefined" != typeof i) return Modernizr;
      t = "function" == typeof t ? t() : t, 1 == r.length ? Modernizr[r[0]] = t : (!Modernizr[r[0]] || Modernizr[r[0]] instanceof Boolean || (Modernizr[r[0]] = new Boolean(Modernizr[r[0]])), Modernizr[r[0]][r[1]] = t), o([(t && 0 != t ? "" : "no-") + r.join("-")]), Modernizr._trigger(e, t);
    }
    return Modernizr;
  }

  function l() {
    return "function" != typeof t.createElement ? t.createElement(arguments[0]) : z ? t.createElementNS.call(t, "http://www.w3.org/2000/svg", arguments[0]) : t.createElement.apply(t, arguments);
  }

  function a() {
    var e = t.body;
    return e || (e = l(z ? "svg" : "body"), e.fake = !0), e;
  }

  function u(e, n, r, i) {
    var o,
        s,
        u,
        f,
        c = "modernizr",
        d = l("div"),
        p = a();
    if (parseInt(r, 10)) for (; r--;) {
      u = l("div"), u.id = i ? i[r] : c + (r + 1), d.appendChild(u);
    }
    return o = l("style"), o.type = "text/css", o.id = "s" + c, (p.fake ? p : d).appendChild(o), p.appendChild(d), o.styleSheet ? o.styleSheet.cssText = e : o.appendChild(t.createTextNode(e)), d.id = c, p.fake && (p.style.background = "", p.style.overflow = "hidden", f = T.style.overflow, T.style.overflow = "hidden", T.appendChild(p)), s = n(d, e), p.fake ? (p.parentNode.removeChild(p), T.style.overflow = f, T.offsetHeight) : d.parentNode.removeChild(d), !!s;
  }

  function f(e, t) {
    return !!~("" + e).indexOf(t);
  }

  function c(e, t) {
    return function () {
      return e.apply(t, arguments);
    };
  }

  function d(e, t, n) {
    var i;

    for (var o in e) {
      if (e[o] in t) return n === !1 ? e[o] : (i = t[e[o]], r(i, "function") ? c(i, n || t) : i);
    }

    return !1;
  }

  function p(e) {
    return e.replace(/([a-z])-([a-z])/g, function (e, t, n) {
      return t + n.toUpperCase();
    }).replace(/^-/, "");
  }

  function m(e) {
    return e.replace(/([A-Z])/g, function (e, t) {
      return "-" + t.toLowerCase();
    }).replace(/^ms-/, "-ms-");
  }

  function h(t, n, r) {
    var i;

    if ("getComputedStyle" in e) {
      i = getComputedStyle.call(e, t, n);
      var o = e.console;
      if (null !== i) r && (i = i.getPropertyValue(r));else if (o) {
        var s = o.error ? "error" : "log";
        o[s].call(o, "getComputedStyle returning null, its possible modernizr test results are inaccurate");
      }
    } else i = !n && t.currentStyle && t.currentStyle[r];

    return i;
  }

  function y(t, r) {
    var i = t.length;

    if ("CSS" in e && "supports" in e.CSS) {
      for (; i--;) {
        if (e.CSS.supports(m(t[i]), r)) return !0;
      }

      return !1;
    }

    if ("CSSSupportsRule" in e) {
      for (var o = []; i--;) {
        o.push("(" + m(t[i]) + ":" + r + ")");
      }

      return o = o.join(" or "), u("@supports (" + o + ") { #modernizr { position: absolute; } }", function (e) {
        return "absolute" == h(e, null, "position");
      });
    }

    return n;
  }

  function g(e, t, i, o) {
    function s() {
      u && (delete q.style, delete q.modElem);
    }

    if (o = r(o, "undefined") ? !1 : o, !r(i, "undefined")) {
      var a = y(e, i);
      if (!r(a, "undefined")) return a;
    }

    for (var u, c, d, m, h, g = ["modernizr", "tspan", "samp"]; !q.style && g.length;) {
      u = !0, q.modElem = l(g.shift()), q.style = q.modElem.style;
    }

    for (d = e.length, c = 0; d > c; c++) {
      if (m = e[c], h = q.style[m], f(m, "-") && (m = p(m)), q.style[m] !== n) {
        if (o || r(i, "undefined")) return s(), "pfx" == t ? m : !0;

        try {
          q.style[m] = i;
        } catch (v) {}

        if (q.style[m] != h) return s(), "pfx" == t ? m : !0;
      }
    }

    return s(), !1;
  }

  function v(e, t, n, i, o) {
    var s = e.charAt(0).toUpperCase() + e.slice(1),
        l = (e + " " + N.join(s + " ") + s).split(" ");
    return r(t, "string") || r(t, "undefined") ? g(l, t, i, o) : (l = (e + " " + L.join(s + " ") + s).split(" "), d(l, t, n));
  }

  function C(e, t, r) {
    return v(e, n, n, t, r);
  }

  var _ = [],
      w = [],
      b = {
    _version: "3.6.0",
    _config: {
      classPrefix: "",
      enableClasses: !0,
      enableJSClass: !0,
      usePrefixes: !0
    },
    _q: [],
    on: function on(e, t) {
      var n = this;
      setTimeout(function () {
        t(n[e]);
      }, 0);
    },
    addTest: function addTest(e, t, n) {
      w.push({
        name: e,
        fn: t,
        options: n
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

  Modernizr.prototype = b, Modernizr = new Modernizr();
  var S = b._config.usePrefixes ? " -webkit- -moz- -o- -ms- ".split(" ") : ["", ""];
  b._prefixes = S;
  var x,
      T = t.documentElement,
      z = "svg" === T.nodeName.toLowerCase();
  !function () {
    var e = {}.hasOwnProperty;
    x = r(e, "undefined") || r(e.call, "undefined") ? function (e, t) {
      return t in e && r(e.constructor.prototype[t], "undefined");
    } : function (t, n) {
      return e.call(t, n);
    };
  }(), b._l = {}, b.on = function (e, t) {
    this._l[e] || (this._l[e] = []), this._l[e].push(t), Modernizr.hasOwnProperty(e) && setTimeout(function () {
      Modernizr._trigger(e, Modernizr[e]);
    }, 0);
  }, b._trigger = function (e, t) {
    if (this._l[e]) {
      var n = this._l[e];
      setTimeout(function () {
        var e, r;

        for (e = 0; e < n.length; e++) {
          (r = n[e])(t);
        }
      }, 0), delete this._l[e];
    }
  }, Modernizr._q.push(function () {
    b.addTest = s;
  });
  var P = l("input"),
      k = "search tel url email datetime date month week time datetime-local number range color".split(" "),
      j = {};

  Modernizr.inputtypes = function (e) {
    for (var r, i, o, s = e.length, l = "1)", a = 0; s > a; a++) {
      P.setAttribute("type", r = e[a]), o = "text" !== P.type && "style" in P, o && (P.value = l, P.style.cssText = "position:absolute;visibility:hidden;", /^range$/.test(r) && P.style.WebkitAppearance !== n ? (T.appendChild(P), i = t.defaultView, o = i.getComputedStyle && "textfield" !== i.getComputedStyle(P, null).WebkitAppearance && 0 !== P.offsetHeight, T.removeChild(P)) : /^(search|tel)$/.test(r) || (o = /^(url|email)$/.test(r) ? P.checkValidity && P.checkValidity() === !1 : P.value != l)), j[e[a]] = !!o;
    }

    return j;
  }(k);

  var E = b.testStyles = u;
  Modernizr.addTest("touchevents", function () {
    var n;
    if ("ontouchstart" in e || e.DocumentTouch && t instanceof DocumentTouch) n = !0;else {
      var r = ["@media (", S.join("touch-enabled),("), "heartz", ")", "{#modernizr{top:9px;position:absolute}}"].join("");
      E(r, function (e) {
        n = 9 === e.offsetTop;
      });
    }
    return n;
  }), Modernizr.addTest("details", function () {
    var e,
        t = l("details");
    return "open" in t ? (E("#modernizr details{display:block}", function (n) {
      n.appendChild(t), t.innerHTML = "<summary>a</summary>b", e = t.offsetHeight, t.open = !0, e = e != t.offsetHeight;
    }), e) : !1;
  });
  var A = "Moz O ms Webkit",
      N = b._config.usePrefixes ? A.split(" ") : [];
  b._cssomPrefixes = N;
  var L = b._config.usePrefixes ? A.toLowerCase().split(" ") : [];
  b._domPrefixes = L;
  var V = {
    elem: l("modernizr")
  };

  Modernizr._q.push(function () {
    delete V.elem;
  });

  var q = {
    style: V.elem.style
  };
  Modernizr._q.unshift(function () {
    delete q.style;
  }), b.testAllProps = v, b.testAllProps = C, Modernizr.addTest("cssgridlegacy", C("grid-columns", "10px", !0)), Modernizr.addTest("cssgrid", C("grid-template-rows", "none", !0)), i(), o(_), delete b.addTest, delete b.addAsyncTest;

  for (var O = 0; O < Modernizr._q.length; O++) {
    Modernizr._q[O]();
  }

  e.Modernizr = Modernizr;
}(window, document);
/******/ })()
;