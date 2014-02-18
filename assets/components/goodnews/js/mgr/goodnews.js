/**
 * Instantiate the GoodNews class
 * 
 * @class GoodNews
 * @extends Ext.Component
 * @param {Object} config An object of config properties
 */
var GoodNews = function(config) {
    config = config || {};
    GoodNews.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews,Ext.Component,{
    window:{},grid:{},panel:{},tabs:{},page:{},combo:{},config:{},msg:{},util:{},form:{},toolbar:{},tree:{}
});
Ext.reg('goodnews',GoodNews);

GoodNews = new GoodNews();
