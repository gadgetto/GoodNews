GoodNews.panel.SystemSettings = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-settings-system'
        ,title: _('goodnews.settings_system_tab')   
        ,layout: 'anchor'
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.settings_system_tab_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
        },{
            layout: 'form'
            ,cls: 'main-wrapper'
            ,labelAlign: 'top'
            ,anchor: '100%'
            ,defaults: {
                msgTarget: 'under'
            }
            ,items: [{
                layout: 'column'
                ,border: false
                ,defaults: {
                    layout: 'form'
                    ,border: false
                }
                ,items: [{
                    columnWidth: 1
                    ,items: [{
                        xtype: 'sliderfield'
                        ,name: 'mailing_bulk_size'
                        ,id: 'mailing_bulk_size'
                        ,ctCls: 'gon-slider-field'
                        ,fieldLabel: _('goodnews.settings_mailing_bulk_size')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_mailing_bulk_size_desc')
                        ,minValue: 1
                        ,maxValue: 100
                        ,increment: 5
                        ,listeners: {
                             'afterrender': {fn: this.updateBulkSizeDisplay, scope: this}
                            ,'change': {fn: this.updateBulkSizeDisplay, scope: this}
                            ,scope: this
                        }
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'mailing_bulk_size'
                        ,html: _('goodnews.settings_mailing_bulk_size_desc')
                        ,cls: 'desc-under'
                    }]
                },{
                    width: 220
                    ,items: [{
                        xtype: 'textfield'
                        ,name: 'mailing_bulk_size_display'
                        ,id: 'mailing_bulk_size_display'
                        ,cls: 'gon-slider-display'
                        ,disabled: true
                        ,anchor: '100%'
                    }]
                }]
            },{
                layout: 'column'
                ,border: false
                ,defaults: {
                    layout: 'form'
                    ,border: false
                }
                ,items: [{
                    columnWidth: 1
                    ,items: [{
                        xtype: 'sliderfield'
                        ,name: 'worker_process_limit'
                        ,id: 'worker_process_limit'
                        ,ctCls: 'gon-slider-field'
                        ,fieldLabel: _('goodnews.settings_worker_process_limit')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_worker_process_limit_desc')
                        ,minValue: 1
                        ,maxValue: 10
                        ,disabled: GoodNews.config.isMultiProcessing ? false : true
                        ,increment: 1
                        ,listeners: {
                             'afterrender': {fn: this.updateWorkerProcessDisplay, scope: this}
                            ,'change': {fn: this.updateWorkerProcessDisplay, scope: this}
                            ,scope: this
                        }
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'worker_process_limit'
                        ,html: _('goodnews.settings_worker_process_limit_desc')
                        ,cls: 'desc-under'
                    }]
                },{
                    width: 220
                    ,items: [{
                        xtype: 'textfield'
                        ,name: 'worker_process_limit_display'
                        ,id: 'worker_process_limit_display'
                        ,cls: 'gon-slider-display'
                        ,disabled: true
                        ,anchor: '100%'
                    }]
                }]
            },{
                xtype: 'xcheckbox'
                ,name: 'worker_process_active'
                ,id: 'worker_process_active'
                ,hideLabel: true
                ,boxLabel: _('goodnews.settings_worker_process_active')
                ,description: MODx.expandHelp ? '' : _('goodnews.settings_worker_process_active_desc')
            },{
                xtype: MODx.expandHelp ? 'label' : 'hidden'
                ,forId: 'worker_process_active'
                ,html: _('goodnews.settings_worker_process_active_desc')
                ,cls: 'desc-under'
            },{
                xtype: 'textfield'
                ,name: 'cron_security_key'
                ,hiddenname: 'cron_security_key'
                ,id: 'cron_security_key'
                ,fieldLabel: _('goodnews.settings_cron_security_key')
                ,description: MODx.expandHelp ? '' : _('goodnews.settings_cron_security_key_desc')
                ,anchor: '100%'
            },{
                xtype: MODx.expandHelp ? 'label' : 'hidden'
                ,forId: 'cron_security_key'
                ,html: _('goodnews.settings_cron_security_key_desc')
                ,cls: 'desc-under'
            }]
        }]    
    });
    GoodNews.panel.SystemSettings.superclass.constructor.call(this,config);
    this.workerProcessLimitDisplay = Ext.getCmp('worker_process_limit_display');
    this.mailingBulkSizeDisplay = Ext.getCmp('mailing_bulk_size_display');
};
Ext.extend(GoodNews.panel.SystemSettings,Ext.Panel,{
    updateWorkerProcessDisplay: function() {
        if (Ext.getCmp('worker_process_limit').getValue() == 1) {
            this.workerProcessLimitDisplay.setValue(_('goodnews.settings_multiprocessing_disabled'));
            //this.workerProcessLimitDisplay.addClass('gon-disabled');
        } else {
            this.workerProcessLimitDisplay.setValue(Ext.getCmp('worker_process_limit').getValue()+_('goodnews.settings_process_max'));
            //this.workerProcessLimitDisplay.removeClass('gon-disabled');
        }
    }
    ,updateBulkSizeDisplay: function() {
        this.mailingBulkSizeDisplay.setValue(Ext.getCmp('mailing_bulk_size').getValue()+_('goodnews.settings_mails_per_bulk'));
    }    
});
Ext.reg('goodnews-panel-settings-system', GoodNews.panel.SystemSettings);
