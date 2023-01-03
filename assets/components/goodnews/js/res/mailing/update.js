/**
 * Loads the update GoodNewsResourceMailing page
 * 
 * @class GoodNewsResource.page.UpdateGoodNewsResourceMailing
 * @extends MODx.page.UpdateResource (/manager/assets/modext/sections/resource/update.js)
 * @param {Object} config An object of config properties
 * @xtype goodnewsresource-page-mailing-update
 */
GoodNewsResource.page.UpdateGoodNewsResourceMailing = function(config) {
    config = config || {record:{}};
    config.record = config.record || {};
    Ext.applyIf(config,{
        panelXType: 'goodnewsresource-panel-mailing'
    });
    config.canDuplicate = false;
    config.canDelete = false;
    GoodNewsResource.page.UpdateGoodNewsResourceMailing.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.page.UpdateGoodNewsResourceMailing,MODx.page.UpdateResource,{
    getButtons: function(config) {
        var buttons = [];
        
        if (config.readOnly == 1) {

            buttons.push({
                text: _('goodnews.mailing_readonly')
                ,id: 'gon-abtn-readonly'
                ,handler: Ext.emptyFn
                ,disabled: true
            });

        } else {

            buttons.push({
                text: config.lockedText || _('locked')
                ,id: 'modx-abtn-locked'
                ,handler: Ext.emptyFn
                ,hidden: (config.canSave == 1)
                ,disabled: true
            });

            buttons.push({
                process: 'resource/update'
                ,text: _('save')
                ,id: 'modx-abtn-save'
                ,cls: 'primary-button'
                ,method: 'remote'
                ,hidden: !(config.canSave == 1)
                //,checkDirty: MODx.request.reload ? false : true
                ,keys: [{
                    key: MODx.config.keymap_save || 's'
                    ,ctrl: true
                }]
            });            
        }
        
        buttons.push({
            text: _('view')
            ,id: 'modx-abtn-preview'
            ,handler: this.preview
            ,scope: this
        });
        
        buttons.push({
            text: _('cancel')
            ,id: 'modx-abtn-cancel'
            ,handler: this.cancel
            ,scope: this
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
    ,cancel: function(btn,e) {
        var fp = Ext.getCmp(this.config.formpanel);
        if (fp && fp.isDirty()) {
            Ext.Msg.confirm(_('warning'),_('resource_cancel_dirty_confirm'),function(e) {
                if (e == 'yes') {
                    fp.warnUnsavedChanges = false;
                    MODx.releaseLock(MODx.request.id);
                    MODx.sleep(400);
                    MODx.loadPage('index', 'namespace=goodnews');
                }
            },this);
        } else {
            MODx.releaseLock(MODx.request.id);
            MODx.loadPage('index', 'namespace=goodnews');
        }
    }
});
Ext.reg('goodnewsresource-page-mailing-update',GoodNewsResource.page.UpdateGoodNewsResourceMailing);
