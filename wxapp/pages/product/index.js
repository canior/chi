// pages/product/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    banners: [],
    products: [],
    page: 1,
    limit: 20,
    hasMore: false,    
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.setNavigationBarTitle({ title: app.globalData.appName })
    this.getProducts(this.data.page)
    app.userActivityCallback = res => {
      app.buriedPoint(options)
    }
  },

  getProducts: function (page) {
    const that = this;
    wx.showLoading({
      title: '玩命加载中',
    })    
    wx.request({
      url: app.globalData.baseUrl + '/products/',
      data: {
        page: page
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var products = that.data.products;
          products.push(...res.data.data.products);
          var hasMore = res.data.data.products.length < that.data.limit ? false : true;
          var nextPage = hasMore ? page + 1 : page;
          that.setData({
            banners: res.data.data.banners,
            products: products,
            page: nextPage,
            hasMore: hasMore
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

  toProductDetail: function(e) {
    const id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '/pages/product/detail?id=' + id,
    })
  },

  redirect: function(e) {
    wx.reLaunch({
      url: e.currentTarget.dataset.url,
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
    if (this.data.hasMore) {
      this.getProducts(this.data.page)
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})