// pages/user/address/edit.js
const app= getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: '',
    name: '',
    phone: '',
    region: [],
    regionText: null,
    customItem: '',
    address: '',
    isDefault: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (options.id) {
      this.getAddress(options.id)
      wx.setNavigationBarTitle({ title: '编辑地址' })
    } else {
      wx.setNavigationBarTitle({ title: '新建地址' })
    }
  },

  getAddress: function(id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/address',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        userAddressId: id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const userAddress = res.data.data.userAddresses
          that.setData({
            id: userAddress.id,
            name: userAddress.name,
            phone: userAddress.phone,
            region: [userAddress.region.province, userAddress.region.city, userAddress.region.county],
            regionText: userAddress.region.province+' '+userAddress.region.city+' '+userAddress.region.county,
            address: userAddress.address,
            isDefault: userAddress.is_default
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

  inputName: function(e) {
    this.setData({
      name: e.detail.value
    })
  },

  inputPhone: function (e) {
    this.setData({
      phone: e.detail.value
    })
  },

  inputAddress: function (e) {
    this.setData({
      address: e.detail.value
    })
  },

  bindRegionChange: function (e) {
    const region = e.detail.value
    this.setData({
      region: region,
      regionText: region[0]+' '+region[1]+' '+region[2]
    })
  },

  setDefault: function(e) {
    this.setData({
      isDefault: !e.currentTarget.dataset.isdefault
    })
  },

  // 保存
  save: function (e) {
    const that = this;
    if (!that.validation()) return;
    wx.request({
      url: app.globalData.baseUrl + '/user/address/post',
      data: {
        userAddressId: this.data.id,
        name: this.data.name,
        phone: this.data.phone,
        province: this.data.region[0],
        city: this.data.region[1],
        county: this.data.region[2],
        address: this.data.address,
        isDefault: this.data.isDefault,
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          //console.log(res.data.data)
          wx.redirectTo({
            url: '/pages/user/address/index',
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

  // 检查输入是否完整
  validation: function () {
    if (!this.data.name) {
      wx.showModal({
        content: '请输入姓名',
        showCancel: false,
      });
      return false;
    }
    if (!this.data.phone) {
      wx.showModal({
        content: '请输入手机号码',
        showCancel: false,
      });
      return false;
    }
    if (!(/^1[34578]\d{9}$/.test(this.data.phone))) {
      wx.showModal({
        content: '手机号码有误',
        showCancel: false,
      });
      return false;
    }
    if (this.data.region.length == 0) {
      wx.showModal({
        content: '请选择省市、区县',
        showCancel: false,
      });
      return false;
    }
    if (!this.data.address) {
      wx.showModal({
        content: '请输入详细地址',
        showCancel: false,
      });
      return false;
    }        
    return true;
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