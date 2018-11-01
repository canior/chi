// pages/group/index.js
const app = getApp()
const productReview = require('../tmpl/productReview.js');
const share = require('../tmpl/share.js');
const bottom = require('../tmpl/bottom.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    user: null,
    userType: 'guest', //游客guest、开团人open、参团人join、其它登录用户other
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    groupOrder: null,
    openUserOrder: null, //开团人订单
    joinUserOrder: null, //参团人订单
    productReviewData: {},
    moreProducts: [],
    shareData: {},
    bottomData: {},
    btnDisabled: false, //防止连击button
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.buriedPoint(options);
    const id = options.id ? options.id : 10145;
    if (id) {
      this.getGroupOrder(id);      
    }
  },

  getGroupOrder: function(id) {
    const that = this;
    const pages = getCurrentPages();
    const currentPageUrl = '/' + pages[pages.length - 1].route;
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/view',
      data: {
        groupOrderId: id,
        url: currentPageUrl
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const groupOrder = res.data.data.groupOrder;
          that.setGroupData(groupOrder);
          share.setShareSources(that, res.data.data.shareSources)          
          if (groupOrder.status != 'completed') {
            // 更多精彩拼团
            that.getMoreProducts();
          } else {
            // 产品评价
            const url = app.globalData.baseUrl + '/products/' + groupOrder.product.id + '/reviews';
            productReview.init(that, url);
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

  // 设置拼团数据，包括用户类型,开团订单,参团订单
  setGroupData: function (groupOrder) {
    var userType = null;
    var openUserOrder = null;
    var joinUserOrder = null;
    // 开团订单,参团订单
    groupOrder.groupUserOrders.forEach((item) => {
        if (item.isMasterOrder) {
          openUserOrder = item
        } else {
          joinUserOrder = item
        }
    })
    // 用户类型
    const user = this.data.user;
    if (user) {//登录用户
      if (user.id == groupOrder.user.id) {//开团人
        userType = 'open';
      } else if (joinUserOrder && joinUserOrder.user.id == user.id) {//参团人
        userType = 'join'
      } else {//其它登录用户
        userType = 'other'
      }
    } else {//游客
      userType = 'guest';
    }
    // 设置数据
    this.setData({
      groupOrder: groupOrder,
      userType: userType,
      openUserOrder: openUserOrder,
      joinUserOrder: joinUserOrder
    })
  },

  // 更多精彩拼团:产品列表同/pages/product/index.js
  getMoreProducts: function () {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/products/',
      data: {
      },
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            moreProducts: res.data.data.products
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) { }
    })
  },

  // 产品评价图片预览
  wxPreviewImage(e) {
    productReview.previewImage(e, this)
  },

  // 我要参团
  joinGroup: function (e) {
    if (this.data.isLogin) {
      this.createJoinUserOrder();
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
    }
  },
  createJoinUserOrder: function () {
    const that = this;
    wx.showLoading({
      title: '载入中',
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
          //console.log(res.data.data)
          //---判断拼团订单是否已被其它参团人抢先支付了
          if (res.data.data.groupUserOrder.status == 'pending') {//继续支付
            wx.redirectTo({
              url: '/pages/group/pay?orderId=' + res.data.data.groupUserOrder.id,
            })
          } else {//已经被抢
            wx.redirectTo({
              url: '/pages/group/index?id=' + this.data.groupOrder.id,
            })
          }
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

  // 分享:邀请好友
  wxShowShareModal: function (e) {
    share.showModal(this)
  },
  wxHideShareModal: function (e) {
    share.hideModal(this)
  },
  wxSaveShareSource: function (e) {
    share.saveShareSource(this, e, app.globalData.baseUrl + '/user/shareSource/create')
  },  

  // 转地址管理
  /*wxUserAddress: function (e) {
    var orderId = null;
    if (this.data.userType == 'open') {
      orderId = this.data.openUserOrder.id
    } else if (this.data.userType == 'join') {
      orderId = this.data.joinUserOrder.id
    }
    if (orderId) {
      wx.navigateTo({
        url: '/pages/user/address/index?orderId=' + orderId,
      })      
    }
  },*/  

  // 转首页
  toHome: function (e) {
    wx.switchTab({
      url: '/pages/product/index',
    })
  },

  // 单独购买提醒弹窗
  wxShowModal: function (e) {
    bottom.showModal(this)
  },
  wxHideModal: function (e) {
    bottom.hideModal(this)
  },
  // 单独购买
  wxCreateOrder: function (e) {
    bottom.createOrder(this, app.globalData.baseUrl + '/groupUserOrder/create', this.data.groupOrder.product.id)
  },
  // 发起拼团
  wxCreateGroup: function (e) {
    bottom.createGroup(this, app.globalData.baseUrl + '/groupOrder/create', this.data.groupOrder.product.id)
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
    bottom.init(this)
    share.init(this)
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
    productReview.getNextPage(this)
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (res) {
    return share.shareObject(this, res)
  }
})