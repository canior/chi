// pages/product/detail.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    imgUrlPrefix: app.globalData.imgUrlPrefix,    
    product: [],
    productReviews: [],
    btnDisabled: false //防止连击button
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const productId = options.id ? options.id : 1;
    this.getProduct(productId);
    this.getProductReview(productId);
    this.setData({
      isLogin: app.globalData.isLogin
    })
  },

  getProduct: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/products/' + id,
      data: {
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            product: res.data.data
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },

  getProductReview: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/products/' + id + '/reviews/',
      data: {
        limit: 5
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            productReviews: res.data.data
          })          
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },

  toHome: function(e) {
    wx.switchTab({
      url: '/pages/product/index',
    })
  },

  openGroup: function(e) {
    if (this.data.isLogin) {
      this.createGroup();
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },
  createGroup: function() {
    const that = this;
    wx.showLoading({
      title: '跳转支付',
      mask: true,
    });
    that.setData({ btnDisabled: true });
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/create',
      data: {
        productId: this.data.product.id,
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const payment = res.data.data.payment;
          wx.requestPayment({

            timeStamp: toString(res.data.data.timeStamp),
            nonceStr: res.data.data.payment.nonceStr,
            package: res.data.data.payment.package,
            signType: res.data.data.payment.signType,
            paySign: res.data.data.payment.paySign,

            success: function (res) { 
              wx.request({
                url: app.globalData.baseUrl + '/groupOrder/notifyPayment',
                data: {
                  isPaid: true,
                  thirdSession: wx.getStorageSync('thirdSession'),
                  groupOrderId: res.data.data.groupOrder.id,
                },
                success: (res) => {
                  if (res.statusCode == 200 && res.data.code == 200) {
                    console.log(res.data.data)
                    wx.redirectTo({
                      url: '/pages/group/index/?id=' + res.data.data.groupOrder.id,
                    })
                  } else {
                    console.log('wx.request return error', res.statusCode);
                  }
                },
                fail(e) {
                  console.log('wx.request /groupOrder/notifyPayment: fail', e)
                },
                complete(e) { }
              })
            },
            fail: function (res) {
              console.log('wx.requestpayment: fail', res)
              wx.showToast({
                title: '支付失败',
              });
              that.setData({ btnDisabled: false });
            },
            complete: function (res) { }
          })          
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
        wx.hideLoading();
        that.setData({ btnDisabled: false });
      },
      complete(e) { }
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
      btnDisabled: false,
      isLogin: app.globalData.isLogin
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