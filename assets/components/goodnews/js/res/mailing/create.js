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
Ext.reg('goodnewsresource-page-mailing-create',GoodNewsResource.page.CreateGoodNewsResourceMailing);
