/**
 * Instantiate the GoodNewsResource class
 * 
 * @class GoodNewsResource
 * @extends Ext.Component
 * @param {Object} config An object of config properties
 */
var GoodNewsResource = function(config) {
    config = config || {};
    GoodNewsResource.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config:{},view:{}
});
Ext.reg('GoodNewsResource',GoodNewsResource);

GoodNewsResource = new GoodNewsResource();
