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
        ].join('\n');

    /* Content of the System box */
    var system = [
        '<h3>'+_('goodnews.system_checks')+'</h3>',
        '<div class="gon-table-wrapper">',
        '<table>',
        '    <thead>',
        '    <tr>',
        '        <th>'+_('goodnews.system_check_title')+'</td>',
        '        <th>'+_('goodnews.system_check_value')+'</td>',
        '    </tr>',
        '    </thead>',
        '    <tbody>',
        '    <tr>',
        '        <td>'+_('goodnews.version')+'</td>',
        '        <td><span class="gon-ok">'+GoodNews.config.componentVersion+'-'+GoodNews.config.componentRelease+'</span></td>',
        '    </tr>',
        '    <tr>',
        '        <td>'+_('goodnews.multi_processing_for_sending')+'</td>',
        '        <td>'+((GoodNews.config.isMultiProcessing) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr>',
        '        <td>'+_('goodnews.imap_extension_available')+'</td>',
        '        <td>'+((GoodNews.config.imapExtension) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr>',
        '        <td>'+_('goodnews.pthumb_addon_installed')+'</td>',
        '        <td>'+((GoodNews.config.pThumbAddOn) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr>',
        '        <td>'+_('goodnews.php_version_required')+(GoodNews.config.requiredPhpVersion)+'</td>',
        '        <td>'+((GoodNews.config.phpVersionOK) ? '<span class="gon-ok">'+(GoodNews.config.actualPhpVersion)+'</span>' : '<span class="gon-nok">'+(GoodNews.config.actualPhpVersion))+'</span></td>',
        '    </tr>',
        '    <tr>',
        '        <td>'+_('goodnews.site_status')+'</td>',
        '        <td>'+((GoodNews.config.siteStatus) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr>',
        '        <td>'+_('goodnews.debug_mode')+'</td>',
        '        <td>'+((GoodNews.config.debug) ? '<span class="gon-nok">'+_('goodnews.activated')+'</span>' : '<span class="gon-ok">'+_('goodnews.deactivated'))+'</span></td>',
        '    </tr>',
        '    </tbody>',
        '</table>',
        '</div>',
       ].join('\n');

    Ext.applyIf(config,{
        id: 'goodnews-panel-settings-about'
        ,title: _('goodnews.settings_about_tab')   
        ,layout: 'anchor'
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.settings_about_tab_desc')+'</p>'
            ,xtype: 'modx-description'
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
