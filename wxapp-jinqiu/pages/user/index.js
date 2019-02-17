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
    textMeta: null
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

  toUpgrade: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/upgrade/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },  

  // 转我的地址
  toUserAddress: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/address/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转我的集Call
  toMyCall: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/call/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转我的订单
  toMyOrder: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/order/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
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
            url: '/pages/user/course/log?id=' + courseId
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
        user: app.globalData.user,
        textMeta: app.globalData.textMeta
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