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
            'new': 'resource/create'
            ,'edit': 'resource/update'
            ,'preview': 'resource/preview'
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
            text: _('goodnews.manage_mailings')
            ,handler: this.loadGoodNewsManagement
            ,id: 'gon-abtn-management'
        });
        btns.push('-');
        if (cfg.canSave == 1) {
            btns.push({
                process: MODx.config.connector_url ? 'resource/update' : 'update'
                ,text: _('save')
                ,method: 'remote'
                ,checkDirty: MODx.request.reload ? false : true
                ,id: 'modx-abtn-save'
                ,keys: [{
                    key: MODx.config.keymap_save || 's'
                    ,ctrl: true
                }]
            });
            btns.push('-');
        } else if (cfg.locked) {
            btns.push({
                text: cfg.lockedText || _('locked')
                ,handler: Ext.emptyFn
                ,id: 'modx-abtn-locked'
                ,disabled: true
            });
            btns.push('-');
        }
        if (cfg.canDelete == 1 && !cfg.locked) {
            btns.push({
                process: 'delete'
                ,text: _('delete')
                ,handler: this.deleteResource
                ,scope:this
                ,id: 'modx-abtn-delete'
            });
            btns.push('-');
        }
        btns.push({
            process: 'preview'
            ,text: _('view')
            ,handler: this.preview
            ,scope: this
            ,id: 'modx-abtn-preview'
        });
        btns.push('-');
        btns.push({
            process: 'cancel'
            ,text: _('cancel')
            ,handler: this.cancel
            ,scope: this
            ,id: 'modx-abtn-cancel'
        });
        btns.push('-');
        btns.push({
            text: _('help_ex')
            ,handler: MODx.loadHelpPane
            ,id: 'modx-abtn-help'
        });
        return btns;
    }
    ,loadGoodNewsManagement: function(btn,e) {
        MODx.loadPage('index', 'namespace=goodnews&id=' + MODx.request.id);
    }
});
Ext.reg('goodnewsresource-page-container-update',GoodNewsResource.page.UpdateGoodNewsResourceContainer);
