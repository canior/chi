// pages/course/detail.js
const app = getApp()
const courseReview = require('../tmpl/courseReview.js');
const share = require('../tmpl/share.js');
const bottom = require('../tmpl/bottom.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    user: null,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    course: null,
    courseReviewData: {},
    bottomData: {},
    shareData: {},
    loading: true,
    groupUserOrder: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    const courseId = options.id ? options.id : 2;
    this.getCourse(courseId);
    const url = app.globalData.baseUrl + '/courses/' + courseId + '/reviews'
    courseReview.init(this, url);
    //app.buriedPoint(options)
    /*app.userActivityCallback = res => {
      app.buriedPoint(options)
      this.setData({
        isLogin: app.globalData.isLogin,
        user: app.globalData.user
      })
    }*/
  },

  getCourse: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/courses/' + id,
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        url: '/pages/course/detail?id=' + id
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var course = res.data.data.product
          course.courseSpecImages.forEach((item) => {
            item.loading = true
          })
          that.setData({
            course: course,
            groupUserOrder: res.data.data.groupUserOrder
          })
          share.setShareSources(that, res.data.data.shareSources)
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },

  // 产品评价图片预览
  wxPreviewImage (e) {
    courseReview.previewImage(e, this)
  },

  // 转首页
  wxHome: function(e) {
    wx.switchTab({
      url: '/pages/course/index',
    })
  },

  // 集Call
  wxCreateGroup: function(e) {
    bottom.createGroup(this, app.globalData.baseUrl + '/groupOrder/create', this.data.course.id)
  },

  // 集Call中
  wxViewGroup: function() {
    wx.redirectTo({
      url: '/pages/group/index?id=' + this.data.groupUserOrder.groupOrderId,
    })
  },

  // 转学员升级
  wxUpgrade: function(e) {
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

  // 观看课程
  wxViewCourse: function () {
    wx.navigateTo({
      url: '/pages/course/video',
    })
  },

  // 集Call
  wxSetCall: function () {
    wx.navigateTo({
      url: '/pages/group/index',
    })
  },

  // 分享:邀请好友
  wxShowShareModal: function (e) {
    share.showModal(this)
  },
  wxHideShareModal: function (e) {
    share.hideModal(this)
  },
  wxSaveShareSource: function (e) {
    share.saveShareSource(this, e, app.globalData.baseUrl + '/user/shareSource/create')
  },

  imgLoadDone: function (e) {
    //console.log('bindload:imgLoadDone', e)
    const index = e.currentTarget.dataset.index
    this.setData({
      ['course.courseSpecImages['+index+'].loading']: false
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
    this.setData({
      isLogin: app.globalData.isLogin,
      user: app.globalData.user
    })
    bottom.init(this)
    share.init(this)
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
    courseReview.getNextPage(this)
  },

  /**
   * 用户点击右上角分享
   * https://mp.weixin.qq.com/cgi-bin/announce?action=getannouncement&announce_id=11526372695t90Dn&version&lang=zh_CN
   * 开发者将无法获知用户是否分享完成
   */
  onShareAppMessage: function (res) {
    return share.shareObject(this, res)
  }
})