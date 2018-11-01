// pages/user/reward/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    menu: ['好友贡献', '活跃', '失效'],
    curIndex: 0,
    isLogin: null,
    user: null,
    rewardList: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  tapMenu: function (e) {
    this.setData({
      curIndex: e.currentTarget.dataset.index
    })
  },
  
  getRewardList: function() {
    const that = this;
    wx.showLoading({
      title: '载入中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/rewards/list',
      data: {
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            rewardList: res.data.data.children
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
      this.getRewardList()
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