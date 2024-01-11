(function () {
    /**
     *
     * This file is used to disable the analytics popup because it seems that WHM UI has a bug with PHP plugins.
     * @see https://github.com/engintron/engintron/issues/1320
     * @see https://forums.cpanel.net/threads/interface-analytics-keeps-appearing-even-after-disabling-cpanel-analytics.687881/
     *
     */
    window.CPANEL = window.CPANEL || {};
    window.CPANEL.analyticsSlideoutHiddenPaths = window.CPANEL.analyticsSlideoutHiddenPaths || [];
    window.CPANEL.analyticsSlideoutHiddenPaths.push(
        /\/crowdsec\/endpoints(\/|$)/,
    );
})();
