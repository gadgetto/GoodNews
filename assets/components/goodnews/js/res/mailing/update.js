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
                ,handler: Ext.emptyFn
                ,id: 'gon-abtn-readonly'
                ,disabled: true
            });
            btns.push('-');
        } else {
            if (cfg.canSave == 1) {
                btns.push({
                    process: MODx.config.connector_url ? 'resource/update' : 'update'
                    ,id: 'modx-abtn-save'
                    ,text: _('save')
                    ,method: 'remote'
                    ,cls: 'primary-button'
                    ,checkDirty: cfg.richtext || MODx.request.activeSave == 1 ? false : true
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
