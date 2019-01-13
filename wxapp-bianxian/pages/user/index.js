// pages/user/index.js
const app = getApp()
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
    //app.buriedPoint(options)
  },

  // 转我的拼团
  toUserGroup: function () {
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/group/index',
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转我的订单
  toUserOrder: function (e) {
    var status = e.currentTarget.dataset.status
    if (this.data.isLogin) {
      wx.navigateTo({
        url: '/pages/user/order/index?status=' + status,
      })
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },

  // 转地址管理
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

  // 转我的收益
  toUserReward: function () {
    wx.switchTab({
      url: '/pages/user/reward/index',
    })
  },

  // 转个人资料
  toMyInfo: function () {
    wx.navigateTo({
      url: '/pages/user/address/edit',
    })
  },
  // 转我的账户
  toMyAccount: function () {
    wx.navigateTo({
      url: '/pages/user/reward/index',
    })
  },
  // 转我的推荐
  toReferral: function () {
    wx.navigateTo({
      url: '/pages/user/referral/index',
    })
  },
  // 转我的名额
  toQuota: function () {
    wx.navigateTo({
      url: '/pages/user/quota/index',
    })
  },
  // 转我的课程
  toMyCourse: function () {
    wx.navigateTo({
      url: '/pages/user/course/index',
    })
  },
  // 转我的学员
  toMyTrainee: function () {
    wx.navigateTo({
      url: '/pages/user/trainee/index',
    })
  },
  // 转学员升级
  toUpgrade: function () {
    wx.navigateTo({
      url: '/pages/user/upgrade/index',
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