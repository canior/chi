// pages/user/course/log.js
const app = getApp()
const share = require('../../tmpl/share.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    options: null,
    course: null,
    courseStudents: null,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    shareData: {},
    isLogin: null,
    user: null,
    groupUserOrderId: null
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
    console.log('/pages/user/course/log: options', options);
    this.setData({
      options: options
    })
  },

  getMyCourseLog: function (id) {
    const that = this;
    wx.showLoading({
      title: '玩命加载中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/groupUserOrder/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        groupUserOrderId: id,
        url: '/pages/course/log?id=' + id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            course: res.data.data.groupUserOrder,
            courseStudents: res.data.data.courseStudents
          })
          share.setShareSources(that, res.data.data.shareSources)
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {},
      complete(e) {
        wx.hideLoading()
      }
    })
  },

  // 评价
  toUserComment: function (e) {
    wx.navigateTo({
      url: '/pages/user/course/review?id=' + this.data.course.id,
    })
  },

  // 分享
  wxShowShareModal: function (e) {
    share.showModal(this)
  },
  wxHideShareModal: function (e) {
    share.hideModal(this)
  },
  wxSaveShareSource: function (e) {
    share.saveShareSource(this, e, app.globalData.baseUrl + '/user/shareSource/create')
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
    if (this.data.isLogin) {
      share.init(this)
      if (this.data.options.id) {
        this.getMyCourseLog(this.data.options.id)
      } else {
        let courseId = null;
        let status = null;
        if (this.data.options.q) {
          // 二维码进入
          const url = decodeURIComponent(this.data.options.q)
          console.log('/pages/user/course/log: url', url)
          courseId = util.getQueryVariable(url, 'courseId');
          status = util.getQueryVariable(url, 'status');
        } else if (this.data.options.courseId && this.data.options.status) {
          // 个人中心－扫一扫
          courseId = this.data.options.courseId;
          status = this.data.options.status;
        }
        this.createCourseStudent(courseId, status);
      }
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 报到或签到
  createCourseStudent: function (courseId, status) {
    const that = this;
    wx.showLoading({
      title: '玩命加载中',
    })    
    wx.request({
      url: app.globalData.baseUrl + '/user/signInCourse',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        courseId: courseId,
        courseStudentStatus: status
      },
      method: 'POST',
      success: (res) => {
        wx.hideLoading()
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          let groupUserOrder = res.data.data.groupUserOrder
          if (util.isEmpty(groupUserOrder)) {
            wx.showModal({
              content: '未找到课程注册订单记录',
              showCancel: false,
              success: function (res) {
                if (res.confirm) wx.switchTab({ url: '/pages/user/index' })
              }
            });
          } else {
            this.getMyCourseLog(groupUserOrder.id)
          }
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { wx.hideLoading() },
      complete(e) { }
    })
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
  onShareAppMessage: function (res) {
    return share.shareObject(this, res)
  }
})