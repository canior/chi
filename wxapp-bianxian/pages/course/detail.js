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
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    course: null,
    courseReviewData: {},
    bottomData: {},
    shareData: {},
    loading: true,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    const courseId = options.id ? options.id : 2;
    this.getCourse(courseId);
    const url = app.globalData.baseUrl + '/products/' + courseId + '/reviews'
    courseReview.init(this, url);
    //app.buriedPoint(options)
    /*app.userActivityCallback = res => {
      app.buriedPoint(options)
      this.setData({
        isLogin: app.globalData.isLogin
      })
    }*/
  },

  getCourse: function (id) {
    const that = this;
    const pages = getCurrentPages();
    const currentPageUrl = '/' + pages[pages.length - 1].route;
    wx.request({
      url: app.globalData.baseUrl + '/products/' + id,
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        url: currentPageUrl
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var course = res.data.data.product
          course.courseSpecImages.forEach((item) => {
            item.loading = true
          })
          that.setData({
            course: course
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

  // 转课程返现详情
  /*toCourseReward: function() {
    wx.navigateTo({
      url: "/pages/course/reward"
    });
  },*/

  // 单独购买提醒弹窗
  wxShowModal: function (e) {
    bottom.showModal(this)
  },
  wxHideModal: function (e) {
    bottom.hideModal(this)
  },
  // 单独购买
  wxCreateOrder: function (e) {
    bottom.createOrder(this, app.globalData.baseUrl + '/groupUserOrder/create', this.data.course.id)
  },
  // 发起拼团
  wxCreateGroup: function(e) {
    bottom.createGroup(this, app.globalData.baseUrl + '/groupOrder/create', this.data.course.id)
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
      isLogin: app.globalData.isLogin
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