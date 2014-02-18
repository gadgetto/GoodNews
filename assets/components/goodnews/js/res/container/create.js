/**
 * Loads the create GoodNewsResourceContainer page
 * 
 * @class GoodNewsResource.page.CreateGoodNewsResourceContainer
 * @extends MODx.page.CreateResource (/manager/assets/modext/sections/resource/create.js)
 * @param {Object} config An object of config properties
 * @xtype goodnewsresource-page-container-create
 */
GoodNewsResource.page.CreateGoodNewsResourceContainer = function(config) {
    config = config || {record:{}};
    config.record = config.record || {};
    Ext.applyIf(config,{
        panelXType: 'goodnewsresource-panel-container'
    });
    config.canDuplicate = false;
    config.canDelete = false;
    GoodNewsResource.page.CreateGoodNewsResourceContainer.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.page.CreateGoodNewsResourceContainer,MODx.page.CreateResource);
Ext.reg('goodnewsresource-page-container-create',GoodNewsResource.page.CreateGoodNewsResourceContainer);
