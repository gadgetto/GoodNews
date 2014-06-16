/**
 * Loads the create GoodNewsResourceMailing page
 * 
 * @class GoodNewsResource.page.CreateGoodNewsResourceMailing
 * @extends MODx.page.CreateResource (/manager/assets/modext/sections/resource/create.js)
 * @param {Object} config An object of config properties
 * @xtype goodnewsresource-page-mailing-create
 */
GoodNewsResource.page.CreateGoodNewsResourceMailing = function(config) {
    config = config || {record:{}};
    config.record = config.record || {};
    Ext.applyIf(config,{
        panelXType: 'goodnewsresource-panel-mailing'
    });
    config.canDuplicate = false;
    config.canDelete = false;
    GoodNewsResource.page.CreateGoodNewsResourceMailing.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.page.CreateGoodNewsResourceMailing,MODx.page.CreateResource,{
    getButtons: function(cfg) {
        var btns = [];
        if (cfg.canSave == 1) {
            btns.push({
                process: version_compare(MODx.config.version, '2.3.0-dev', '>=') ? 'resource/create' : 'create'
                ,id: 'modx-abtn-save'
                ,text: _('save')
                ,method: 'remote'
                ,checkDirty: true
                ,keys: [{
                    key: MODx.config.keymap_save || 's'
                    ,ctrl: true
                }]
            });
            btns.push('-');
        }
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
                    MODx.releaseLock(MODx.request.id);
                    MODx.sleep(400);
                    MODx.loadPage(''+MODx.action['goodnews:index'])
                }
            },this);
        } else {
            MODx.releaseLock(MODx.request.id);
            MODx.loadPage(''+MODx.action['goodnews:index'])
        }
    }
});
Ext.reg('goodnewsresource-page-mailing-create',GoodNewsResource.page.CreateGoodNewsResourceMailing);
