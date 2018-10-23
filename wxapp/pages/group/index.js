// pages/group/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    showModal: false,
    imgUrlPrefix: app.globalData.imgUrlPrefix,    
    groupOrder: null,
    user: null,
    isOpenUser: null, //是否开团人
    btnDisabled: false //防止连击button    
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const id = options.id;
    const isLogin = app.globalData.isLogin;
    if (isLogin && id) {
      this.getGroupOrder(id);
    }
  },

  getGroupOrder: function(id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/view',
      data: {
        groupOrderId: id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const groupOrder = res.data.data.groupOrder;
          const user = app.globalData.user;
          var isOpenUser = user && user.id == groupOrder.user.id;
          that.setData({
            groupOrder: groupOrder,
            user: user,
            isOpenUser: isOpenUser
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

  joinGroup: function (e) {
    if (this.data.isLogin) {
      this.createGroupOrder();
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },
  createGroupOrder: function () {
    const that = this;
    wx.showLoading({
      title: '跳转支付',
      mask: true,
    });
    that.setData({ btnDisabled: true });
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/join',
      data: {
        groupOrderId: this.data.groupOrder.id,
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const payment = res.data.data.payment;
          const groupOrderId = res.data.data.groupOrder.id;
          wx.requestPayment({
            timeStamp: payment.timeStamp.toString(),
            nonceStr: payment.nonceStr,
            package: payment.package,
            signType: payment.signType,
            paySign: payment.paySign,
            success: function (res) {
              wx.request({
                url: app.globalData.baseUrl + '/groupOrder/notifyPayment',
                data: {
                  isPaid: true,
                  thirdSession: wx.getStorageSync('thirdSession'),
                  groupOrderId: groupOrderId,
                },
                method: 'POST',
                success: (res) => {
                  if (res.statusCode == 200 && res.data.code == 200) {
                    console.log(res.data.data)
                    wx.redirectTo({
                      url: '/pages/group/index?id=' + res.data.data.groupOrder.id,
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

  // 继续拼团
  toProductDetail: function() {
    wx.navigateTo({
      url: '/pages/product/detail?id=' + this.data.groupOrder.product.id,
    })
  },

  // 邀请好友
  showModal: function(e) {
    this.setData({
      showModal: true
    })
  },
  hideModal: function (e) {
    this.setData({
      showModal: false
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
    return {
      title: "分享标题",
      imageUrl: '',
      path: '/pages/group/index?id=' + this.data.groupOrder.id
    }
  }
})