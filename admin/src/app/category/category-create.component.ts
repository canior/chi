import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
import { Router, ActivatedRoute } from '@angular/router';
import { NzMessageService,UploadFile,NzModalService } from 'ng-zorro-antd';
import { HttpService } from '../shared/shared.module';
import { Category } from '../model/category';
import {environment } from '../../environments/environment';

@Component({
	selector: 'app-category-create',
	templateUrl: './category-create.component.html',
	styleUrls: ['./category-create.component.less']
})
export class CategoryCreateComponent implements OnInit {

	constructor(
		private activeRoute: ActivatedRoute,
		private http: HttpService,
		private router: Router,
        private NzModal: NzModalService,
        private message: NzMessageService
	) { }


	id:string;
	isLoading = true;
	selectCategory = new Category;
    categorys = [];


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

    	let url = '/category/create';

        //网络请求
        this.http.get( url,{id:this.id} )
            .then( (res:any ) => {

                this.categorys = res.data.categorys;

                if( this.id ){
                    this.selectCategory = res.data.category;

                    if( this.selectCategory.remark_image ){
                        let item =  {
                            uid: 1,
                            name: this.selectCategory.remark_image,
                            status: 'done',
                            url: this.selectCategory.remark_image_url
                        };
                        this.remarkFile = [item];
                    }

                    if( this.selectCategory.preview_image ){
                        let item =  {
                            uid: 0,
                            name: this.selectCategory.preview_image,
                            status: 'done',
                            url: this.selectCategory.preview_image_url
                        };
                        this.previewFile = [item];
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

        let url = '/category/new';
        if( this.id ){
            url = '/category/update';
        }

        this.http.post(url, this.selectCategory )
            .then( (res:any ) => {
                if (res.code == 0) {
                    this.message.create('success',res.msg);
                    this.router.navigate(['backend/category']);
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
    showUploadList = {
        showPreviewIcon: true,
        showRemoveIcon: true,
        hidePreviewIconInNonImage: true
    };
    previewImage: string | undefined = '';
    previewVisible = false;



    remarkFile = [];
    // 监听文件选择器
    handleRemarkFile(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCategory.remark_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectCategory.remark_image = '';
        }else if( info.file.response != undefined && info.file.response.msg ){
            this.message.error(info.file.response.msg);
        }
    }


    // 文件上传
    previewFile = [];
    // 监听文件选择器
    handleVideoFile(info:any): void {
        if( info.file.response != undefined && info.file.response.fileId ){
            this.selectCategory.preview_image = info.file.response.fileId;
        }else if( info.type != undefined && info.type == "removed" ){
            this.selectCategory.preview_image = '';
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
