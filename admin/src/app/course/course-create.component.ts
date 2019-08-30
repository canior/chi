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
    subjects = [
        {"title":"变现思维课",value:"THINKING"},
        {"title":"变现思维系统直通课",value:"TRADING"},
        {"title":"变现系统课I",value:"SYSTEM_1"},
        {"title":"变现系统课II",value:"SYSTEM_2"},
        {"title":"变现系统课III",value:"SYSTEM_3"},
        {"title":"私董",value:"PRIVATE_DIRECTOR"},
    ];


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
                    if( this.selectCourse.preview_image ){
                        var item =  {
                            uid: 0,
                            name: this.selectCourse.preview_image,
                            status: 'done',
                            url: this.selectCourse.preview_image_url
                        };
                        this.videoFile = [item];
                    }

                    if( this.selectCourse.share_image ){
                        var item =  {
                            uid: 1,
                            name: this.selectCourse.share_image,
                            status: 'done',
                            url: this.selectCourse.share_image_url
                        };
                        this.shareFile = [item];
                    }

                    if( this.selectCourse.content_image ){
                        this.contentFile = [];
                        for (var i = this.selectCourse.content_image.length - 1; i >= 0; i--) {
                            var item =  {
                                uid: 3+i,
                                name: this.selectCourse.content_image[i],
                                status: 'done',
                                url: this.selectCourse.content_image_url[i]
                            };
                            this.contentFile.push(item);
                        }
                    }

                    if( this.selectCourse.spec_image ){
                        this.spebFileList = [];
                        for (var i = this.selectCourse.spec_image.length - 1; i >= 0; i--) {
                            var item =  {
                                uid: 3+i,
                                name: this.selectCourse.spec_image[i],
                                status: 'done',
                                url: this.selectCourse.spec_image_url[i]
                            };
                            this.spebFileList.push(item);
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


    videoFile = [];
    // 监听文件选择器
    handleVideoFile(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCourse.preview_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectCourse.preview_image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }

    // 文件上传
    shareFile = [];
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
    contentFile = [];
    // 监听文件选择器
    handleContentFile(info:any): void {
        console.log(info.file.name);
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCourse.content_image.push(info.file.response.fileId);
        }else if( info.type != undefined && info.type == "removed" ){
            var data = [];
            for (var i = this.selectCourse.content_image.length - 1; i >= 0; i--) {
                if( this.selectCourse.content_image[i] != info.file.name ){
                    data.push(info.file.name);
                }
            }
            this.selectCourse.content_image = data;
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


    spebFileList = [];
    // 监听文件选择器
    handleSpecFile(info:any): void {
        console.log(info.file.name);
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCourse.spec_image.push(info.file.response.fileId);
        }else if( info.type != undefined && info.type == "removed" ){
            var data = [];
            for (var i = this.selectCourse.spec_image.length - 1; i >= 0; i--) {
                if( this.selectCourse.spec_image[i] != info.file.name ){
                    data.push(info.file.name);
                }
            }
            this.selectCourse.spec_image = data;
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
