// pages/group/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    user: null,
    userType: 'guest', //游客guest、开团人opener、参团人joiner、其它登录用户other
    joinUser: null, //参团人user（注：拼团完成后才有实际的参团人）
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    groupOrder: null,
    products: [],
    productReviews: [],
    page: 1,
    limit: 5,
    hasMore: false,
    showModal: false,
    btnDisabled: false //防止连击button
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const id = options.id;
    if (id) {
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
          that.setGroupData(groupOrder);
          //---
          if (groupOrder.status == 'completed') {
            that.getProducts();
          } else {
            that.getProductReview(groupOrder.product.id, that.data.page);
          }
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },

  // 设置拼团数据，包括用户类型及是否有参团人
  setGroupData: function (groupOrder) {
    var userType = null;
    var joinUser = null;
    // 是否有参团人
    if (groupOrder.status == 'completed') {
      groupOrder.groupUserOrders.forEach((item) => {
        if (!item.isMasterOrder) joinUser = item.user
      })
    }
    // 用户类型
    const user = this.data.user;
    if (user) {// 登录用户
      if (user.id == groupOrder.user.id) {// 开团人
        userType = 'opener';
      } else if (joinUser && joinUser.id == user.id) {// 参团人
        userType = 'joiner'
      } else {// 其它登录用户
        userType = 'other'
      }
    } else {// 游客
      userType = 'guest';
    }
    // 设置数据
    this.setData({
      groupOrder: groupOrder,
      userType: userType,
      joinUser: joinUser
    })    
  },

  // copy from /pages/product/index.js
  getProducts: function () {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/products/',
      data: {
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            products: res.data.data.products,
            //banners: res.data.data.banners
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) { }
    })
  },

  // copy from /pages/product/detail.js
  getProductReview: function (id, page) {
    const that = this;
    wx.showLoading({
      title: '玩命加载中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/products/' + id + '/reviews',
      data: {
        page: page
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var productReviews = that.data.productReviews;
          productReviews.push(...res.data.data);
          var hasMore = res.data.data.length < that.data.limit ? false : true;
          var nextPage = hasMore ? page + 1 : page;
          that.setData({
            productReviews: productReviews,
            page: nextPage,
            hasMore: hasMore
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) {
        wx.hideLoading()
      }
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

  // 继续拼团/我要拼团
  toProductDetail: function(e) {
    var id = e.currentTarget.dataset.id
    if (!id) {
      id = this.data.groupOrder.product.id
    }
    wx.navigateTo({
      url: '/pages/product/detail?id=' + id,
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

  toUserAddress: function (e) {
    wx.navigateTo({
      url: "/pages/user/address/index",
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
    if (this.data.hasMore) {
      this.getProductReview(this.data.product.id, this.data.page)
    }
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