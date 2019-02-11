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
    joinUserOrders: null, //参团人订单
    productReviewData: {},
    moreProducts: [],
    shareData: {},
    bottomData: {},
    btnDisabled: false, //防止连击button
    countdown: {hr: '00', min: '00', sec: '00'}, //倒计时数据
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const id = options.id;
    if (id) {
      this.getGroupOrder(id);      
    }
    app.buriedPoint(options)
    /*app.userActivityCallback = res => {
      app.buriedPoint(options)
      this.setData({
        isLogin: app.globalData.isLogin,
        user: app.globalData.user
      })
    }*/
  },

  getGroupOrder: function(id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),        
        groupOrderId: id,
        url: '/pages/group/index?id=' + id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var groupOrder = res.data.data.groupOrder;
          that.setGroupData(groupOrder);
          share.setShareSources(that, res.data.data.shareSources)          
          if (groupOrder.status == 'completed') {//拼团完成
            // 更多精彩拼团
            that.setData({
              moreProducts: []//res.data.data.product.similarProducts
            })
          } else {//拼团未完成:pending, expired
            // 产品评价
            const url = app.globalData.baseUrl + '/products/' + groupOrder.product.id + '/reviews';
            productReview.init(that, url);
            // pending: 拼团倒计时
            that.countdown()
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

  // pending: 拼团倒计时
  countdown: function() {
    const groupOrder = this.data.groupOrder
    const expiredAt = new Date(groupOrder.expiredAt);
    const now = new Date();
    console.log('expiredAt:'+expiredAt+', now:'+now)
    var totalSecond = Math.floor((expiredAt - now) / 1000);
    if (groupOrder.status == 'pending') {
      var interval = setInterval(function () {
        // 总秒数
        var second = totalSecond;
        // 时钟位
        var hr = Math.floor(second / 3600);
        var hrStr = hr.toString();
        if (hrStr.length == 1) hrStr = '0' + hrStr;
        // 分钟位
        var min = Math.floor((second - hr * 3600) / 60);
        var minStr = min.toString();
        if (minStr.length == 1) minStr = '0' + minStr;
        // 秒位
        var sec = second - hr * 3600 - min * 60;
        var secStr = sec.toString();
        if (secStr.length == 1) secStr = '0' + secStr;
        this.setData({
          countdown: {hr: hrStr, min: minStr, sec: secStr}
        });
        totalSecond--;
        if (totalSecond < 0) {
          clearInterval(interval);
          // 倒计时完成
          this.setData({
            countdown: {hr: '00', min: '00', sec: '00' }
          });
          this.expireGroupOrder()
        }
      }.bind(this), 1000);
    }
  },

  expireGroupOrder: function() {
    const that = this;
    const groupOrder = that.data.groupOrder
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/expire',
      data: {
        groupOrderId: groupOrder.id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const groupOrder = res.data.data.groupOrder;
          that.setGroupData(groupOrder);
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
    var joinUserOrders = [];
    // 开团订单,参团订单
    groupOrder.groupUserOrders.forEach((item) => {
        if (item.isMasterOrder) {
          openUserOrder = item
        } else {
          joinUserOrders.push(item)
        }
    })
    // 用户类型
    const user = this.data.user;
    if (user) {//登录用户
      if (user.id == groupOrder.user.id) {//开团人
        userType = 'open';
      } else if (joinUserOrders.length > 0 && this.in_array(joinUserOrders, user.id)) {//参团人
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
      joinUserOrders: joinUserOrders
    })
  },

  in_array: function (joinUserOrders, useId) {
    for (var i in joinUserOrders) {
      if (joinUserOrders[i].user.id == useId) {
        return true;
      }
    }
    return false;
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
        console.log(res.data.data)
        if (res.statusCode == 200 && res.data.code == 200) {
          wx.redirectTo({
            url: '/pages/group/index?id=' + that.data.groupOrder.id,
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

  // 转首页
  wxHome: function (e) {
    wx.switchTab({
      url: '/pages/product/index',
    })
  },

  // 转产品返现详情
  toProductReward: function () {
    wx.navigateTo({
      url: "/pages/product/reward"
    });
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