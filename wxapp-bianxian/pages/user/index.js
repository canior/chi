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
      wx.navigateTo({
        url: '/pages/user/upgrade/index',
      })
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
        console.log(res);
        var tmp = res.result.split('id=');
        var id = tmp[1];
        wx.navigateTo({
          url: '/pages/user/course?id=' + id + '&qr=1', //qr=1表示扫码进入
        });
      }
    });
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