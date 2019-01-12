// pages/user/reward/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    menu: [
      { name: '好友贡献', isValid: null },
      { name: '活跃', isValid: true },
      { name: '失效', isValid: false }
    ],
    isValid: null,
    isLogin: null,
    user: null,
    rewardList: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.buriedPoint(options)
  },

  tapMenu: function (e) {
    console.log(e)
    this.getRewardList(e.currentTarget.dataset.isvalid)
  },
  
  getRewardList: function (isValid) {
    const that = this;
    wx.showLoading({
      title: '载入中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/rewards/list',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        isValid: isValid
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            rewardList: res.data.data.children,
            isValid: isValid
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) {
        wx.hideLoading()
      }
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
      this.getRewardList(this.data.isValid)
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

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})