var __data = {
  showModal: false,
  btnDisabled: false //防止连击button
}

function init(that) {
  __enableBtn(that)
  hideModal(that)
}

function __enableBtn(that) {
  __data.btnDisabled = false;
  that.setData({
    bottomData: __data
  });  
}

function __disableBtn(that) {
  __data.btnDisabled = true;
  that.setData({
    bottomData: __data
  });
}

function showModal(that) {
  __data.showModal = true;
  that.setData({
    bottomData: __data
  });
}

function hideModal(that) {
  __data.showModal = false;
  that.setData({
    bottomData: __data
  });
}

// 单独购买
function createOrder(that, url, productId) {
  if (that.data.isLogin) {
    __createOrder(that, url, productId);
  } else {
    wx.navigateTo({
      url: '/pages/user/login',
    })
  }
}
function __createOrder(that, url, productId) {
  wx.showLoading({
    title: '载入中',
    mask: true,
  });
  __disableBtn(that)
  wx.request({
    url: url,
    data: {
      productId: productId,
      thirdSession: wx.getStorageSync('thirdSession'),
    },
    method: 'POST',
    success: (res) => {
      wx.hideLoading();
      if (res.statusCode == 200 && res.data.code == 200) {
        //console.log(res.data.data)
        wx.navigateTo({
          url: '/pages/group/pay?orderId=' + res.data.data.groupUserOrder.id,
        })
      } else {
        console.log('wx.request return error', res.statusCode);
      }
    },
    fail(e) {
      wx.hideLoading();
      __enableBtn(that)
    },
    complete(e) { }
  })
}

// 发起拼团
function createGroup(that, url, productId) {
  if (that.data.isLogin) {
    __createGroup(that, url, productId);
  } else {
    wx.navigateTo({
      url: '/pages/user/login',
    })
  }
}
function __createGroup(that, url, productId) {
  wx.showLoading({
    title: '载入中',
    mask: true,
  });
  __disableBtn(that)
  wx.request({
    url: url,
    data: {
      productId: productId,
      thirdSession: wx.getStorageSync('thirdSession'),
    },
    method: 'POST',
    success: (res) => {
      wx.hideLoading();
      if (res.statusCode == 200 && res.data.code == 200) {
        //console.log(res.data.data)
        wx.navigateTo({
          url: '/pages/group/pay?orderId=' + res.data.data.groupUserOrder.id,
        })
      } else {
        console.log('wx.request return error', res.statusCode);
      }
    },
    fail(e) {
      wx.hideLoading();
      __enableBtn(that)
    },
    complete(e) { }
  })
}

module.exports = {
  init: init,
  showModal: showModal,
  hideModal: hideModal,
  createOrder: createOrder,
  createGroup: createGroup
}