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
    });
    config.canDuplicate = false;
    config.canDelete = false;
    GoodNewsResource.page.UpdateGoodNewsResourceContainer.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.page.UpdateGoodNewsResourceContainer,MODx.page.UpdateResource,{
    getButtons: function(config) {
        var buttons = [];
        
        buttons.push({
            text: '<i class="icon icon-envelope"></i>&nbsp;&nbsp;'+_('goodnews.manage_mailings')
            ,id: 'gon-abtn-management'
            ,handler: this.loadGoodNewsManagement
        });

        buttons.push({
            process: 'Resource/Update'
            ,text: _('save')
            ,id: 'modx-abtn-save'
            ,cls: 'primary-button'
            ,method: 'remote'
            ,hidden: !(config.canSave == 1)
            ,keys: [{
                key: MODx.config.keymap_save || 's'
                ,ctrl: true
            }]
        },{
            text: (config.lockedText || '<i class="icon icon-lock"></i>')
            ,id: 'modx-abtn-locked'
            ,handler: Ext.emptyFn
            ,hidden: (config.canSave == 1)
            ,disabled: true
        });
        
        if (config.canDuplicate == 1 && (config.record.parent !== parseInt(MODx.config.tree_root_id) || config.canCreateRoot == 1)) {
            buttons.push({
                text: _('duplicate')
                ,id: 'modx-abtn-duplicate'
                ,handler: this.duplicateResource
                ,scope: this
            });
        }
        
        buttons.push({
            text: _('view')
            ,id: 'modx-abtn-preview'
            ,handler: this.preview
            ,hidden: config.record.deleted
            ,scope: this
        },{
            text: _('cancel')
            ,id: 'modx-abtn-cancel'
            ,handler: this.cancel
            ,scope: this
        });
        
        if (config.canDelete == 1 && !config.locked) {
            buttons.push({
                text: '<i class="icon icon-repeat"></i>'
                ,id: 'modx-abtn-undelete'
                ,handler: this.unDeleteResource
                ,hidden: !config.record.deleted
                ,scope: this
            });
        
            buttons.push({
                text: '<i class="icon icon-trash-o"></i>'
                ,id: 'modx-abtn-delete'
                ,handler: this.deleteResource
                ,hidden: config.record.deleted
                ,scope: this
            });
        }
        
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
    
    ,loadGoodNewsManagement: function(btn,e) {
        MODx.loadPage('index', 'namespace=goodnews&id=' + MODx.request.id);
    }
});
Ext.reg('goodnewsresource-page-container-update',GoodNewsResource.page.UpdateGoodNewsResourceContainer);
