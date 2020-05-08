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
    getButtons: function(cfg) {
        var btns = [];
        
        if (cfg.readOnly == 1) {
            
            btns.push({
                text: _('goodnews.mailing_readonly')
                ,id: 'gon-abtn-readonly'
                ,handler: Ext.emptyFn
                ,disabled: true
            });
            
        } else {
            
            btns.push({
                text: cfg.lockedText || _('locked')
                ,id: 'modx-abtn-locked'
                ,handler: Ext.emptyFn
                ,hidden: (cfg.canSave == 1)
                ,disabled: true
            });

            btns.push({
                process: 'resource/update'
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
