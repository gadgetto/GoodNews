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
Ext.extend(GoodNewsResource.page.CreateGoodNewsResourceContainer,MODx.page.CreateResource,{
    getButtons: function(cfg) {
        var btns = [];
        
        if (cfg.canSave == 1) {
            btns.push({
                process: 'resource/create'
                ,reload: true
                ,text: _('save')
                ,id: 'modx-abtn-save'
                ,cls:'primary-button'
                ,method: 'remote'
                //,checkDirty: true
                ,keys: [{
                    key: MODx.config.keymap_save || 's'
                    ,ctrl: true
                }]
            });
        }
        
        btns.push({
            process: 'cancel'
            ,text: _('cancel')
            ,handler: this.cancel
            ,scope: this
            ,id: 'modx-abtn-cancel'
        });
        
        btns.push({
            text: '<i class="icon icon-question-circle icon-lg"></i>&nbsp;' + _('help_ex')
            ,id: 'modx-abtn-help'
            ,handler: function(){
                MODx.helpUrl = GoodNewsResource.helpUrl;
                MODx.loadHelpPane();
            }
        });
        
        return btns;
    }
});
Ext.reg('goodnewsresource-page-container-create',GoodNewsResource.page.CreateGoodNewsResourceContainer);
