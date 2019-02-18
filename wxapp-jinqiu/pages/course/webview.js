// pages/course/webview.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: null
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    if (options.title) wx.setNavigationBarTitle({ title: options.title })
    //options.scene = encodeURIComponent('ss=123&p=456&go=789');
    const productId = options.id ? options.id : app.parseScene(options, 'p')
    this.setData({
      url: app.globalData.baseUrl + '/v?id=' + productId
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