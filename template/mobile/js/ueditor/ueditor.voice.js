UE.registerUI('dialog',function(editor,uiName){

    //创建dialog
    var dialog = new UE.ui.Dialog({
        //指定弹出层中页面的路径，这里只能支持页面,因为跟addCustomizeDialog.js相同目录，所以无需加路径
        iframeUrl:'../addons/maixun_pte/template/mobile/js/ueditor/voiceRecord.html',
        //需要指定当前的编辑器实例
        editor:editor,
        //指定dialog的名字
        name:uiName,
        //dialog的标题
        title:"录音",

        //指定dialog的外围样式
        cssRules:"width:600px;height:200px;",

        //如果给出了buttons就代表dialog有确定和取消
        buttons:[]
    });

    //参考addCustomizeButton.js
    var btn = new UE.ui.Button({
        name:'上传录音' + uiName,
        title:'上传录音',
        //需要添加的额外样式，指定icon图标，这里默认使用一个重复的icon
        cssRules :'background-position: -20px -40px;',
        onclick:function () {
            //渲染dialog
            dialog.render();
            dialog.open();
        }
    });

    return btn;
});