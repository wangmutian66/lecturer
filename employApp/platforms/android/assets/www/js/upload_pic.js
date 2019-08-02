(function() {
	// 事件绑定
	$("#upload").bind("click",function() {
		takePicture().then(uploadPicture).then(deletePictureFromCache);
	});
	
	// 打卡摄像头拍照
	function takePicture() {
		var deferred  = when.defer(),
			destinationType=navigator.camera.DestinationType,
			options = {
				quality: 100,
				destinationType: destinationType.FILE_URI
				//sourceType: Camera.PictureSourceType.PHOTOLIBRARY,
				//cameraDirection: Camera.Direction.FRONT,
				//targetWidth: 240,
				//targetHeight: 320,
				//correctOrientation: true
		};
		
		navigator.camera.getPicture(function(data){
			deferred.resolve(data);
		}, null, options);
		
		return deferred.promise
	}
	
	// 上传图片到服务器
	function uploadPicture( imageURI ){
		var deferred  = when.defer(),
			options = new FileUploadOptions();
		options.fileKey = "file",
		var timestamp = Date.parse(new Date());
        options.fileName = timestamp+".jpeg";//文件名 
		options.mimeType = "image/jpeg";
		
		var ft = new FileTransfer();
		// 上传回调
		ft.onprogress = showUploadingProgress;
		navigator.notification.progressStart("", "当前上传进度");
		ft.upload( imageURI, encodeURI('http://10.4.45.90/upload/upload_file.php'), function(){ 
			deferred.resolve( imageURI );
			navigator.notification.progressStop();
		} , null, options);
		return deferred.promise
	}
	
	// 显示上传进度
	function showUploadingProgress( progressEvt ){
		if( progressEvt.lengthComputable ){
			navigator.notification.progressValue( Math.round( ( progressEvt.loaded / progressEvt.total ) * 100) );
		}
	}
	
	// 从缓存中删除图片
	function deletePictureFromCache( imageURI ){
		window.resolveLocalFileSystemURI(fileURI, function( fileEntry ){
			fileEntry.remove();
		}, null);
	}
	
})();