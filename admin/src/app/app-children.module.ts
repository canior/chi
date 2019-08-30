import { NgModule } from '@angular/core';

import { LoginModule } from './login/login.module';
import { DashboardModule } from './dashboard/dashboard.module';

import { ActivityListModule   } from './activity/activity-list.module';
import { ActivityCreateModule } from './activity/activity-create.module';

import { CourseListModule   } from './course/course-list.module';
import { CourseCreateModule } from './course/course-create.module';

import { CategoryModule } from './category/category.module';
import { CategoryCreateModule } from './category/category-create.module';


import { AlbumModule } from './album/album.module';

@NgModule({
	imports: [
	    DashboardModule,
	    LoginModule,
	    CourseListModule,
	    CourseCreateModule,
	    ActivityListModule,
	    ActivityCreateModule,
	    CategoryCreateModule,
	    AlbumModule,
	    CategoryModule
	]
})
export class AppChildrenModule { }
