// pages/share/moment.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imageUrl: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      imageUrl: options.imageUrl ? decodeURIComponent(options.imageUrl) : 'https://laowantong.yunlishuju.com/image/preview/2'
    })
  },

  tapSaveShareImage: function() {
    const that = this
    wx.showLoading({
      title: '图片下载中',
      mask: true,
    });
    wx.downloadFile({
      url: this.data.imageUrl,
      success: function (res) {
        wx.hideLoading();
        if (res.statusCode === 200) {
          that.saveShareImage(res.tempFilePath)
        }
      },
      fail: function (res) {
        wx.hideLoading()
      }
    })
  },

  saveShareImage: function(imagePath) {
    const that = this;
    app.unifiedAuth(
      'scope.writePhotosAlbum',
      '要保存图片到你的相册，是否允许？',
      function () {
        wx.saveImageToPhotosAlbum({
          filePath: imagePath,
          success: function () {
            wx.showModal({
              title: '',
              content: '图片保存成功，快去分享到朋友圈吧',
              showCancel: false,
              success(res) {
                if (res.confirm) {
                  wx.navigateBack()
                }
              }
            })
          }
        })
      }
    )
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