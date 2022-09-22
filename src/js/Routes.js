Ext.define('TualoOffice.routes.DS',{
    url: 'hbk',
    handler: {
        action: function(type,tablename,id){
            type='views';
            let tablenamecase = tablename.toLocaleUpperCase().substring(0,1) + tablename.toLowerCase().slice(1);
            console.log('Tualo.DataSets.'+type+'.'+tablenamecase,arguments);
            let opt = {};
            if (typeof id!='undefined'){ 
                opt.loadId=id;
            }
            TualoOffice.getApplication().addView('Tualo.DataSets.'+type+'.'+tablenamecase,tablename,true,opt);

        },
        before: function (type,tablename,xid,action) {
            let tablenamecase = tablename.toLocaleUpperCase().substring(0,1) + tablename.toLowerCase().slice(1);
            let id = null;
            if (typeof xid.resume=='function'){ action=xid; }else{ id = xid;}
            /*
            Ext.require([
                'Tualo.DataSets.model.'+tablenamecase
                'Tualo.DataSets.store.'+tablenamecase,
                'Tualo.DataSets.'+type+'.'+tablenamecase
            ],function(){
                action.resume();
            },this);
            */
            action.resume();
        }
    }
});