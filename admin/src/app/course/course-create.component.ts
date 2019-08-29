import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
import { Router, ActivatedRoute } from '@angular/router';
import { NzMessageService,UploadFile,NzModalService } from 'ng-zorro-antd';
import { HttpService } from '../shared/shared.module';
import { Course } from '../model/course';
import {environment } from '../../environments/environment';

@Component({
	selector: 'app-course-create',
	templateUrl: './course-create.component.html',
	styleUrls: ['./course-create.component.less']
})
export class CourseCreateComponent implements OnInit {

	constructor(
		private activeRoute: ActivatedRoute,
		private http: HttpService,
		private router: Router,
        private NzModal: NzModalService,
        private message: NzMessageService
	) { }


	id:string;
	isLoading = true;
	selectCourse = new Course;
	category = [];
    teacher = [];

	ngOnInit() {

        this.activeRoute.queryParams.subscribe(params => {
            if(params.id && params.id != '' ) {
                this.id = params.id;
            }
            this.initData();
        });
    }
    // 提交表单
    


    // 初始化基础数据
    initData() {

    	this.isLoading = false;

    	let url = '/course/create';

        //网络请求
        this.http.get( url,{id:this.id} )
            .then( (res:any ) => {
                this.category = res.data.category;
                this.teacher = res.data.teacher;

                if( this.id ){
                    this.selectCourse = res.data.course;
                    if( this.selectCourse.image ){
                        var item =  {
                            uid: 0,
                            name: this.selectCourse.image,
                            status: 'done',
                            url: this.selectCourse.image_url
                        };
                        this.imageFileList = [item];
                    }

                    if( this.selectCourse.share_image ){
                        var item =  {
                            uid: 1,
                            name: this.selectCourse.share_image,
                            status: 'done',
                            url: this.selectCourse.share_image_url
                        };
                        this.shareFileList = [item];
                    }

                    if( this.selectCourse.video_image ){
                        var item =  {
                            uid: 2,
                            name: this.selectCourse.video_image,
                            status: 'done',
                            url: this.selectCourse.video_image_url
                        };
                        this.shareFileList = [item];
                    }

                    if( this.selectCourse.content_image ){
                        this.contentFileList = [];
                        for (var i = this.selectCourse.content_image.length - 1; i >= 0; i--) {
                            var item =  {
                                uid: 3+i,
                                name: this.selectCourse.content_image[i],
                                status: 'done',
                                url: this.selectCourse.content_image_url[i]
                            };
                            this.contentFileList.push(item);
                        }
                    }
                }

            }).catch((msg : string) => {
                this.message.error(msg);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }

    doSubmit(){

        this.isLoading = true;

        let url = '/course/new';
        if( this.id ){
            url = '/course/update';
        }

        this.http.post(url, this.selectCourse )
            .then( (res:any ) => {
                if (res.code == 0) {
                    this.message.create('success',res.msg);
                    this.router.navigate(['backend/course']);
                } else {
                    this.message.create('error',res.msg);
                }
            }).catch((msg : string) => {
                this.message.create('error',msg);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }



    // 文件上传
    file_upload_url = environment.api+'/file/ngupload';
    showeUpload = {
        showPreviewIcon: true,
        showRemoveIcon: true,
        hidePreviewIconInNonImage: true
    };
    previewImage: string | undefined = '';
    previewVisible = false;


    imageFileList = [];
    // 监听文件选择器
    handleImageFile(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCourse.image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectCourse.image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }

    // 文件上传
    shareFileList = [];
    // 监听文件选择器
    handleShareFile(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCourse.share_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectCourse.share_image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


    // 文件上传
    videoFileList = [];
    // 监听文件选择器
    handleVideoFile(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCourse.video_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectCourse.video_image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }

    contentFileList = [];
    // 监听文件选择器
    handleContentFile(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCourse.content_image.push(info.file.response.fileId);
        }else if( info.type != undefined && info.type == "removed" ){
            var data = [];
            for (var i = this.selectCourse.content_image.length - 1; i >= 0; i--) {
                if( this.selectCourse.content_image[i].id != info.file.response.fileId ){
                    data.push(info.file.response.fileId);
                }
            }
            this.selectCourse.content_image = data;
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }

    //
    config: any = {
        height: 250,
        theme: 'modern',
        plugins: 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image imagetools link media template codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists textcolor wordcount contextmenu colorpicker textpattern',
        image_advtab: true,
        imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage imageoptions',
    };
}
