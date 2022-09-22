Ext.define('Ext.cmp.cmp_hbk.Viewport', {
  extend: 'Ext.tualo.ApplicationPanel',
  requires: [
    //'IconFont.form.field.ComboBox',
    //'Ext.cmp.cmp_hbk.models.Viewport',
    //'Ext.cmp.cmp_hbk.controller.Viewport'
  ],
  layout: 'fit',
  listeners: {
    boxReady: 'onBoxReady',
    resize: 'onResize'
  },
  /*
  controller: 'cmp_hbk_viewport',
  viewModel: {
      type: 'cmp_hbk_viewport'
  },
  */
  items: [
    {
      xtype: 'hybrid_views_portal'
    }
  ],
  routeTo: function(val){

  },
  statics: {
    canRouteTo: function(val) {
      var r = Ext.cmp.cmp_template_default.controller.Viewport.requestParams(val);
      if (typeof r=='object'){
        if (typeof r.t=='string'){
            if (typeof Ext.ClassManager.get('Tualo.DataSets.views.'+r.t)=='function'){
                return true; 
            }
        }
      }
      console.warn('DS not accessible',val);
      return false;
    }
  }
});
