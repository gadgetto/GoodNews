GoodNews.panel.AboutSettings = function(config) {
    config = config || {};

    /* Content of the About box */
    var about = [
        '<h3>'+GoodNews.config.componentName+' '+GoodNews.config.componentVersion+'-'+GoodNews.config.componentRelease+'</h3>',
        '<p>',
        _('goodnews.desc')+'<br />',
        '&copy; by '+GoodNews.config.developerName+'<br />',
        '<a href="'+GoodNews.config.developerUrl+'">'+GoodNews.config.developerUrl+'</a>',
        '</p>',
       ].join('\n');

    /* Content of the Credits box */
    var credits = [
        '<h3>'+_('goodnews.credits')+'</h3>',
        '<p>'+_('goodnews.credits_modx_community')+'</p>',
        '<p>'+_('goodnews.credits_bob_ray')+'</p>',
        '<p>'+_('goodnews.credits_icons_by')+'</p>',
        ].join('\n');

    /* Content of the System box */
    var system = [
        '<h3>'+_('goodnews.system_checks')+'</h3>',
        '<table class="gon-syscheck-tbl">',
        '    <thead>',
        '    <tr>',
        '        <th class="gon-syscheck-tbl-lcol">'+_('goodnews.system_check_title')+'</td>',
        '        <th class="gon-syscheck-tbl-rcol">'+_('goodnews.system_check_value')+'</td>',
        '    </tr>',
        '    </thead>',
        '    <tbody>',
        '    <tr class="gon-odd">',
        '        <td>'+_('goodnews.multi_processing_for_sending')+'</td>',
        '        <td>'+((GoodNews.config.isMultiProcessing) ? _('goodnews.ok') : _('goodnews.nok'))+'</td>',
        '    </tr>',
        '    <tr class="gon-even">',
        '        <td>'+_('goodnews.imap_extension_available')+'</td>',
        '        <td>'+((GoodNews.config.imapExtension) ? _('goodnews.ok') : _('goodnews.nok'))+'</td>',
        '    </tr>',
        '    <tr class="gon-odd">',
        '        <td>'+_('goodnews.site_status')+'</td>',
        '        <td>'+((GoodNews.config.siteStatus) ? _('goodnews.ok') : _('goodnews.nok'))+'</td>',
        '    </tr>',
        '    </tbody>',
        '</table>',
       ].join('\n');

    Ext.applyIf(config,{
        id: 'goodnews-panel-settings-about'
        ,title: _('goodnews.settings_about_tab')   
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.settings_about_tab_desc')+'</p>'
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
                    columnWidth: .5
                    ,items: [{
                        xtype: 'container'
                        ,autoEl: 'div'
                        ,cls: 'gon-about-box'
                        ,html: about
                    },{
                        xtype: 'container'
                        ,autoEl: 'div'
                        ,cls: 'gon-about-box'
                        ,html: credits
                    }]
                },{
                    columnWidth: .5
                    ,items: [{
                        xtype: 'container'
                        ,autoEl: 'div'
                        ,cls: 'gon-about-box'
                        ,html: system
                    }]
                }]
            }]
        }]
    });
    GoodNews.panel.AboutSettings.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.AboutSettings,Ext.Panel);
Ext.reg('goodnews-panel-settings-about', GoodNews.panel.AboutSettings);
