// pages/user/index.js
const app = getApp()
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: null,
    user: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
  },

  // 转个人资料
  toUserInfo: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/info/update',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转我的账户
  toMyAccount: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/account/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转我的推荐
  toReferral: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/referral/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转我的名额
  toQuota: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/quota/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转我的课程
  toMyCourse: function () {
    wx.switchTab({
      url: '/pages/user/course/index',
    })
  },  

  // 转我的学员
  toMyStudent: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/student/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转学员升级
  toUpgrade: function () {
    if (this.data.isLogin) {
      // 判断个人资料是否完整
      if (this.data.user.isCompletedPersonalInfo) {
        wx.navigateTo({
          url: '/pages/user/upgrade/index',
        })
      } else {
        // 转新建个人资料
        wx.navigateTo({
          url: '/pages/user/info/update?upgrade=1',
        })
      }
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },  

  // 扫一扫
  toScan: function () {
    wx.scanCode({
      onlyFromCamera: true,
      success: (res) => {
        console.log(res)
        //var courseId = util.getQueryVariable(res.result, 'courseId');
        //var status = util.getQueryVariable(res.result, 'status');
        if (res.result) {
          //https://bianxian.yunlishuju.com/backend/course/student/1
          let index = res.result.lastIndexOf("\/");
          let id = res.result.substring(index + 1, res.result.length);
          console.log('wx.scanCode:id=', id);
          this.createCourseStudent(id, null);
        } else {
          console.log('wx.scanCode:fail')
        }
      }
    });
  },

  // 报到或签到
  createCourseStudent: function (courseId, status) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/signInCourse',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        courseId: courseId,
        courseStudentStatus: status
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          wx.navigateTo({
            url: '/pages/user/course/log?id=' + res.data.data.groupUserOrder.id
          });
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },  

  toLogin: function () {
    wx.navigateTo({
      url: '/pages/user/login',
    })
  },
  
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    app.userActivityCallback = res => {
      this.setData({
        isLogin: app.globalData.isLogin,
        user: app.globalData.user
      })
    }
    app.getUserInfo();
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})