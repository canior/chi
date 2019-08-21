import { NgModule } from '@angular/core';

import { LoginModule } from './login/login.module';
import { DashboardModule } from './dashboard/dashboard.module';

import { CourseListModule   } from './course/course-list.module';
import { CourseCreateModule } from './course/course-create.module';

import { CategoryModule } from './category/category.module';
import { AlbumModule } from './album/album.module';

@NgModule({
	imports: [
	    DashboardModule,
	    LoginModule,
	    CourseListModule,
	    CourseCreateModule,
	    AlbumModule,
	    CategoryModule
	]
})
export class AppChildrenModule { }
