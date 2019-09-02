// Model
export class Course {
	public id:string;
	public title:string;
	public subject:string;
	public update_at:string;
	public category_id:any;
	public category_title:string;
	public start_date:string;
	public end_date:string;
	public category_name:string;
	public teacher_id:string;
	public teacher_name:string;
	public top:string;
	public priority:string;
	
	public checked:any;
	public is_single:any;
	public is_recommend:any;
	public is_new:any;

	public cost_type:any;
	public price:any;
	public collect_timelong:any;
	public collect_num:any;
	
	public show_type:any;

	public video_image:any;
	public video_image_url:any;
	public share_image:any;
	public share_image_url:any;

	public remark_image = [];
	public remark_image_url = [];
	public content_image = [];
	public content_image_url = [];

	public remark:any;
	public content:any;
	public video_key:any;
	public status:any;
	public unlockType:any;
	public album_title:any;
	public album_id:any;
	
	public link:any;
	public course_tag:any;
	
	public unlock_type:any; 
}