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
    getButtons: function(config) {
        var buttons = [];

        if (config.canSave == 1) {
            buttons.push({
                process: 'Resource/Create'
                ,reload: true
                ,text: _('save')
                ,id: 'modx-abtn-save'
                ,cls:'primary-button'
                ,method: 'remote'
                ,keys: [{
                    key: MODx.config.keymap_save || 's'
                    ,ctrl: true
                }]
            });
        }
        
        buttons.push({
            process: 'cancel'
            ,text: _('cancel')
            ,handler: this.cancel
            ,scope: this
            ,id: 'modx-abtn-cancel'
        });
        
        buttons.push({
            text: '<i class="icon icon-question-circle"></i>'
            ,id: 'modx-abtn-help'
            ,handler: function(){
                MODx.helpUrl = GoodNewsResource.helpUrl;
                MODx.loadHelpPane();
            }
        });

        return buttons;
    }
});
Ext.reg('goodnewsresource-page-container-create',GoodNewsResource.page.CreateGoodNewsResourceContainer);
