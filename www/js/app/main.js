define(['config'], function (config) {
    require(['app'], function (App) {
        App.Init(config.wsDomain, config.webDomain, config.protocol, config.maxMsgLength);
    });
});