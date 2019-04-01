// pages/offlineCourse/detail.js
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
    eligible: false,
    eligibleViewer: false,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    course: null,
    courseReviewData: {},
    bottomData: {},
    shareData: {},
    loading: true,
    groupUserOrderId: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    const courseId = options.id ? options.id : app.parseScene(options, 'p')
    this.getCourse(courseId);
    const url = app.globalData.baseUrl + '/offlineCourses/' + courseId + '/reviews'
    courseReview.init(this, url);
    app.buriedPoint(options)
  },

  getCourse: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/offlineCourses/' + id,
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        url: '/pages/offlineCourse/detail?id=' + id
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log('id='+id, res.data.data)
          var course = res.data.data.product
          course.courseSpecImages.forEach((item) => {
            item.loading = true
          })
          // eligible
          let eligible = false;
          if (course.eligibleUserLevels) {
            let userLevel = that.data.user ? that.data.user.bianxianUserLevel : null;
            course.eligibleUserLevels.forEach((level) => {
              if (level == userLevel) { eligible = true }
            })
          }
          // eligibleViewer
          let eligibleViewer = false;
          if (course.eligibleViewer) {
            let userLevel = that.data.user ? that.data.user.userLevel : null;
            course.eligibleViewer.forEach((level) => {
              if (level == userLevel) { eligibleViewer = true }
            })
          }
          that.setData({
            course: course,
            eligible: eligible,
            eligibleViewer: eligibleViewer,
            groupUserOrderId: res.data.data.groupUserOrder ? res.data.data.groupUserOrder.id : null
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

  // 单独购买提醒弹窗
  wxShowModal: function (e) {
    bottom.showModal(this)
  },
  wxHideModal: function (e) {
    bottom.hideModal(this)
  },

  // 单独购买
  wxCreateOfflineCourse: function (e) {
    bottom.createOfflineCourse(this, app.globalData.baseUrl + '/groupUserOrder/createOfflineCourse', this.data.course.productId)
  },
  
  // 转学员升级
  wxUpgrade: function(e) {
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

  // 转课程日志
  wxToCourseLog: function () {
    if (this.data.isLogin) {
      wx.redirectTo({
        url: '/pages/user/offlineCourse/log?id=' + this.data.groupUserOrderId,
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
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
    if (this.data.isLogin) {
      bottom.init(this)
      share.init(this)
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
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