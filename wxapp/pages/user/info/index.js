// pages/user/info/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgUrlPrefix: app.globalData.imgUrlPrefix,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
  },

  toPersonal: function () {
    wx.navigateTo({
      url: '/pages/user/info/update',
    })
  },

  toQRCode: function () {
    const that = this;
    wx.showLoading({
      title: '玩命加载中',
    })    
    wx.request({
      url: app.globalData.baseUrl + '/user/viewUserQrCard',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        url: '/pages/course/index'
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const shareSources = res.data.data.shareSources;
          const shareSource = shareSources['quan'];
          wx.navigateTo({
            url: '/pages/share/moment?imageUrl=' + encodeURIComponent(that.data.imgUrlPrefix + '/' + shareSource.bannerFileId),
          })
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