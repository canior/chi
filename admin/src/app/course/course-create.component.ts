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

    	let url = '/course/createData/0';
        if( this.id ){
            url = '/course/createData/'+this.id;
        }

        //网络请求
        this.http.get( url,{} )
            .then( (res:any ) => {
                this.category = res.data.category;
                this.teacher = res.data.teacher;
            }).catch((msg : string) => {
                this.message.error(msg);
            })
            .finally( () => {
                this.isLoading = false;
            })
    }

    doSubmit(){

        this.isLoading = true;

        let url = '/course';
        if( this.id ){
            url = '/course/update/'+this.id;
        }

        this.http.post(url, this.selectCourse )
            .then( (res:any ) => {
                if (res.code == 0) {
                    this.message.create('success',res.msg);
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


    handlePreview = (file: UploadFile) => {
        this.previewImage = file.url || file.thumbUrl;
        this.previewVisible = true;
    };

    // 监听文件选择器
    handleChange(info:any): void {
        // console.log(info.type);
        if( info.file.response != undefined && info.file.response.data ){
            this.course.image = info.file.response.data;
        }else if( info.type != undefined && info.type == "removed" ){
            this.course.image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


    // 文件上传
    file_upload_url = environment.api+'/common/upload';
    showUploadList = {
        showPreviewIcon: true,
        showRemoveIcon: true,
        hidePreviewIconInNonImage: true
    };
    fileList = [];
    previewImage: string | undefined = '';
    previewVisible = false;
}
