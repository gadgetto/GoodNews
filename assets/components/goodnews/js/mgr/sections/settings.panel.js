/**
 * Ext.form.SliderField does not listen to the changecomplete-event of the slider!
 * This is a fix which calls the event handler with the proper arguments (slider, value, thumb), 
 * and also enables a "changecomplete" listener
 */
Ext.sequence(Ext.form.SliderField.prototype,'initComponent',function(){
	this.slider.on('change',this.fireEvent.createDelegate(this,'change',0));
	this.slider.on('changecomplete',this.fireEvent.createDelegate(this,'changecomplete',0));
});


/**
 * FormPanel to handle settings tabs (tabs container).
 * 
 * @class GoodNews.SettingsPanel
 * @extends MODx.FormPanel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-settings
 */
GoodNews.SettingsPanel = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'goodnews-panel-settings'
        ,cls: 'container'
        ,bodyStyle: ''
        ,unstyled: true
        ,items: [{
            html: '<h2 class="gon-cmp-header gon-logo">'+_('goodnews.settings')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype: 'modx-tabs'
            ,itemId: 'tabs'
			,cls: 'structure-tabs'
            ,stateful: true
            ,stateId: 'goodnews-panel-settings'
            ,stateEvents: ['tabchange']
            ,getState: function() {
                return {
                    activeTab:this.items.indexOf(this.getActiveTab())
                };
            }
            ,items: [{
                xtype: 'goodnews-panel-settings-general'
            },{
                xtype: 'goodnews-panel-settings-container'
            }/*,{
                xtype: 'goodnews-panel-settings-bounceparsingrules'
            }*/,{
                xtype: 'goodnews-panel-settings-system'
            },{
                xtype: 'goodnews-panel-settings-about'
            }]
        }]
    });
    GoodNews.SettingsPanel.superclass.constructor.call(this,config);
    this.init();
};
Ext.extend(GoodNews.SettingsPanel,MODx.FormPanel,{
    init: function(){
        this.actionToolbar = new Ext.Toolbar({
            renderTo: 'modAB'
            ,id: 'modx-action-buttons'
            ,defaults: { scope: this }
            ,items: this.getButtons()
        });                                
        this.actionToolbar.doLayout();
        this.getSettings();
    }
    ,getButtons: function() {
        var buttons = [];
        buttons.push({
            text: _('goodnews.settings_save_button')
            ,id: 'button-settings-save'
            ,iconCls: GoodNews.config.legacyMode ? 'gon-icn-save' : ''
            ,handler: this.updateSettings
            ,scope: this
            ,cls: 'primary-button'
        })
        buttons.push({
            text: _('goodnews.settings_close_button')
            ,id: 'button-settings-close'
            ,iconCls: GoodNews.config.legacyMode ? 'gon-icn-close' : ''
            ,handler: this.closeSettings
            ,scope: this
        },'-')
        buttons.push({
            text: _('help_ex')
            ,id: 'button-help'
            ,iconCls: GoodNews.config.legacyMode ? 'gon-icn-help' : ''
            ,handler: function(){
                MODx.config.help_url = GoodNews.config.helpUrl;
                MODx.loadHelpPane();
            }
            ,scope: this
        })
        return buttons;
    }
    ,getSettings: function(){
        this.getForm().load({
            url: GoodNews.config.connectorUrl
            ,params: {
                action: 'mgr/settings/get'
            }
            ,waitMsg: _('goodnews.msg_loading')
            ,success: function(){
                //console.info(data);
            }
            ,failure: function(results,request){
                Ext.MessageBox.alert(_('goodnews.msg_failed'),result.responseText);
            }
            ,scope: this
        });
    }
    ,updateSettings: function(){
        this.getForm().submit({
            url: GoodNews.config.connectorUrl
            ,params: {
                action: 'mgr/settings/update'
            }
            ,waitMsg: _('goodnews.msg_saving')
            ,success: function(form,action){
                if(action.result.success){
                    // show success status message
                    MODx.msg.status({
                        title: _('save_successful')
                        ,message: _('goodnews.msg_saving_successfull')
                        ,delay: 3
                    });
                }
            }
            ,failure: function(result,request) {
                Ext.MessageBox.alert(_('goodnews.msg_failed'),result.responseText);
            }
            ,scope: this
        });
    }
    ,closeSettings: function(){
        location.href = MODx.config.manager_url + '?a=' + MODx.request.a;
    }
});
Ext.reg('goodnews-panel-settings',GoodNews.SettingsPanel);
