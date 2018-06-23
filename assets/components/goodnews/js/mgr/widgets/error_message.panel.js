GoodNews.panel.MessageError = function(config) {
    config = config || {};

    /* Content of the Messages box */    
    var messageList = GoodNews.config.setupErrors;
    var keys = Object.keys(messageList);
    keys.sort();
    
    var messagesOutput = '';

    for (var i = 0; i < keys.length; i++) {
        if (messageList.hasOwnProperty(keys[i])) {
            messagesOutput+='<li>'+messageList[keys[i]]['description']+'</li>';
        }
    }
    
    var messages = [
        '<h3>'+_('goodnews.error_messages')+'</h3>',
        '<ul>'+messagesOutput+'</ul>',
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
        '    <tr class="gon-even">',
        '        <td>'+_('goodnews.version')+'</td>',
        '        <td><span class="gon-ok">'+GoodNews.config.componentVersion+'-'+GoodNews.config.componentRelease+'</span></td>',
        '    </tr>',
        '    <tr class="gon-odd">',
        '        <td>'+_('goodnews.multi_processing_for_sending')+'</td>',
        '        <td>'+((GoodNews.config.isMultiProcessing) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr class="gon-even">',
        '        <td>'+_('goodnews.imap_extension_available')+'</td>',
        '        <td>'+((GoodNews.config.imapExtension) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr class="gon-odd">',
        '        <td>'+_('goodnews.pthumb_addon_installed')+'</td>',
        '        <td>'+((GoodNews.config.pThumbAddOn) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr class="gon-even">',
        '        <td>'+_('goodnews.php_version_required')+(GoodNews.config.requiredPhpVersion)+'</td>',
        '        <td>'+((GoodNews.config.phpVersionOK) ? '<span class="gon-ok">'+(GoodNews.config.actualPhpVersion)+'</span>' : '<span class="gon-nok">'+(GoodNews.config.actualPhpVersion))+'</span></td>',
        '    </tr>',
        '    <tr class="gon-odd">',
        '        <td>'+_('goodnews.site_status')+'</td>',
        '        <td>'+((GoodNews.config.siteStatus) ? '<span class="gon-ok">'+_('yes')+'</span>' : '<span class="gon-nok">'+_('no'))+'</span></td>',
        '    </tr>',
        '    <tr class="gon-even">',
        '        <td>'+_('goodnews.debug_mode')+'</td>',
        '        <td>'+((GoodNews.config.debug) ? '<span class="gon-nok">'+_('goodnews.activated')+'</span>' : '<span class="gon-ok">'+_('goodnews.deactivated'))+'</span></td>',
        '    </tr>',
        '    </tbody>',
        '</table>',
       ].join('\n');

    Ext.applyIf(config,{
        id: 'goodnews-panel-error-message'
        ,title: _('goodnews.error_message_tab')   
        ,layout: 'anchor'
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.error_message_tab_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc gon-panel-desc-error'
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
                        ,cls: 'gon-about-box gon-about-box-error'
                        ,html: messages
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
    GoodNews.panel.MessageError.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.MessageError,Ext.Panel);
Ext.reg('goodnews-panel-error-message', GoodNews.panel.MessageError);
