export default class ObserverUtil {
  /**
   * Obverses the element and calls the callback if the element comes on view
   */
  observeElement(element, callback) {
    if ("IntersectionObserver" in window) {
      this._observeElementIntersectionObserver(element, callback);
    } else {
      this._observeElementLegacy(element, callback);
    }
  }

  /**
   * Observes the element with the build in observer feature
   * @param element
   * @param callback
   * @private
   */
  _observeElementIntersectionObserver(element, callback) {
    let lazyElementObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          callback();
          lazyElementObserver.unobserve(entry.target);
        }
      });
    });

    lazyElementObserver.observe(element);
  }

  /**
   * Checks if the element is in view and calls the callback (support for legacy browsers)
   * @param element
   * @param callback
   */
  _observeElementLegacy(element, callback) {
    let active = false;

    const lazyLoad = function () {
      if (active === false) {
        active = true;

        window.setTimeout(function () {
          if (
            (
              element.getBoundingClientRect().top <= window.innerHeight &&
              element.getBoundingClientRect().bottom >= 0
            ) &&
            getComputedStyle(element).display !== "none"
          ) {
            callback();
            document.removeEventListener("scroll", lazyLoad);
            window.removeEventListener("resize", lazyLoad);
            window.removeEventListener("orientationchange", lazyLoad);
          }

          active = false;
        }, 200);
      }
    };

    document.addEventListener("scroll", lazyLoad);
    window.addEventListener("resize", lazyLoad);
    window.addEventListener("orientationchange", lazyLoad);
  }

}
