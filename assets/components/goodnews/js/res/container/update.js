/**
 * Loads the update GoodNewsResourceContainer page
 * 
 * @class GoodNewsResource.page.UpdateGoodNewsResourceContainer
 * @extends MODx.page.UpdateResource (/manager/assets/modext/sections/resource/update.js)
 * @param {Object} config An object of config properties
 * @xtype goodnewsresource-page-container-update
 */
GoodNewsResource.page.UpdateGoodNewsResourceContainer = function(config) {
    config = config || {record:{}};
    config.record = config.record || {};
    Ext.applyIf(config,{
        panelXType: 'goodnewsresource-panel-container'
        ,actions: {
            'new': 'Resource/Create'
            ,'edit': 'Resource/Update'
            ,'preview': 'Resource/Preview'
        }
    });
    config.canDuplicate = false;
    config.canDelete = false;
    GoodNewsResource.page.UpdateGoodNewsResourceContainer.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.page.UpdateGoodNewsResourceContainer,MODx.page.UpdateResource,{
    getButtons: function(cfg) {
        var btns = [];
        
        btns.push({
            text: cfg.lockedText || _('locked')
            ,id: 'modx-abtn-locked'
            ,handler: Ext.emptyFn
            ,hidden: (cfg.canSave == 1)
            ,disabled: true
        });

        btns.push({
            text: _('goodnews.manage_mailings')
            ,handler: this.loadGoodNewsManagement
            ,id: 'gon-abtn-management'
        });
        
        btns.push({
            process: 'Resource/Update'
            ,text: _('save')
            ,id: 'modx-abtn-save'
            ,cls: 'primary-button'
            ,method: 'remote'
            ,hidden: !(cfg.canSave == 1)
            //,checkDirty: MODx.request.reload ? false : true
            ,keys: [{
                key: MODx.config.keymap_save || 's'
                ,ctrl: true
            }]
        });
        
        if (cfg.canDelete == 1 && !cfg.locked) {
            btns.push({
                text: _('delete')
                ,id: 'modx-abtn-delete'
                ,handler: this.deleteResource
                ,scope:this
            });
        }
        
        btns.push({
            text: _('view')
            ,id: 'modx-abtn-preview'
            ,handler: this.preview
            ,scope: this
        });
        
        btns.push({
            text: _('cancel')
            ,id: 'modx-abtn-cancel'
            ,handler: this.cancel
            ,scope: this
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
    ,loadGoodNewsManagement: function(btn,e) {
        MODx.loadPage('index', 'namespace=goodnews&id=' + MODx.request.id);
    }
});
Ext.reg('goodnewsresource-page-container-update',GoodNewsResource.page.UpdateGoodNewsResourceContainer);
