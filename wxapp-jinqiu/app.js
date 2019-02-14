//app.js
App({
  onLaunch: function () {
    // 获取或更新用户信息
    this.getUserInfo();
  },

  // 获取或更新用户信息
  getUserInfo: function () {
    const that = this
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称，不会弹框
          wx.getUserInfo({
            success: res => {
              // 可以将 res 发送给后台解码出 unionId
              console.log('app:getUserInfo', res.userInfo);
              that.globalData.userInfo = res.userInfo
              that.login(res.userInfo, function () {
                // 首页Page.onLoad的buriedPoint
                if (that.userActivityCallback) {
                  that.userActivityCallback(res)
                }
              });
            }
          })
        } else {
          // 未授权或已取消授权
          console.log("app:authSetting['scope.userInfo']=false");
          that.login(null, function () {
            // 首页Page.onLoad的buriedPoint
            if (that.userActivityCallback) {
              that.userActivityCallback(res)
            }
          });
        }
      }
    })
  },

  // 登录: 创建新用户或记录老用户登录信息, 返回thirdSession
  login: function (userInfo = null, callback = null) {
    const that = this;
    let thirdSession = wx.getStorageSync('thirdSession');
    wx.login({
      success: res => {
        // 发送 res.code 到后台换取 openId, sessionKey, unionId        
        //console.log('app:wx.login', res)
        wx.request({
          url: that.globalData.baseUrl + '/user/login',
          data: {
            code: res.code,
            thirdSession: thirdSession ? thirdSession : null,
            nickName: userInfo ? userInfo.nickName : null,
            avatarUrl: userInfo ? userInfo.avatarUrl : null,
            userInfo: userInfo
          },
          method: 'POST',
          success: (res) => {
            console.log('app:wx.request /user/login', res);
            if (res.data.code == 200 && res.data.msg == 'login_success') {
              const thirdSession = res.data.data.thirdSession
              wx.setStorageSync('thirdSession', thirdSession);
              that.globalData.isLogin = that.isLogin();
              that.globalData.user = res.data.data.user;
              if (thirdSession && callback) {
                callback()
              }
            }
          },
          fail(e) {
            console.log('app:wx.request /user/login fail', e);
          },
          complete(e) { }
        })
      }
    })
  },

  // 判断是否授权并登录
  isLogin: function () {
    if (this.globalData.userInfo && wx.getStorageSync('thirdSession')) {
      return true;
    } else {
      return false;
    }
  },
  
  // 统一请求授权
  unifiedAuth(authKey, prompt, callbak) {
    const that = this
    // 判断授权
    wx.getSetting({
      success(res) {
        //console.log(res.authSetting[authKey]);
        if (res.authSetting[authKey] || res.authSetting[authKey] == undefined) {//保持授权或首次进入
          callbak()
        } else {//前面拒绝授权
          wx.showModal({
            title: '授权设置',
            content: prompt,
            success: function (res) {
              if (res.confirm) {//用户允许
                wx.openSetting({
                  success: function (res) {
                    console.log('wx.openSetting:succ', res);
                    if (res.authSetting[authKey]) {
                      callbak()
                    } else {
                      wx.showToast({
                        title: '授权失败',
                        icon: 'success',
                        duration: 1000
                      })
                    }
                  },
                  fail: function (e) {
                    console.log('wx.openSetting:fail', e);
                  }
                })
              }
              if (res.cancel) {//用户拒绝
                wx.showToast({
                  title: '授权失败',
                  icon: 'success',
                  duration: 1000
                })
              }
            }
          })//wx.showModal
        }
      }
    })
  },

  // 埋点请求函数
  buriedPoint(options) {
    var pages = getCurrentPages(); //页面栈
    var currentPageUrl = '/' + pages[pages.length - 1].route; //加载的页面url
    var that = this;
    const thirdSession = wx.getStorageSync('thirdSession')
    if (!thirdSession) return;
    //console.log('buriedPoint: url=' + currentPageUrl + ', thirdSession=' + thirdSession)
    wx.request({
      url: that.globalData.baseUrl + '/user/activity/add',
      method: 'POST',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        url: currentPageUrl,
        version: '1.0'
      },
      success: function (res) {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log('buriedPoint => addShareSource: ', options)
          if (options && options.shareSourceId) {
            that.addShareSource(options.shareSourceId)
          }
        }
      },
      fail(e) {},
      complete(e) {}
    });
  },

  // 记录用户来源
  addShareSource(shareSourceId) {    
    var that = this;
    wx.request({
      url: that.globalData.baseUrl + '/user/shareSource/addUser',
      method: 'POST',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        shareSourceId: shareSourceId
      },
      success: function (res) {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data.shareSourceUser)
        }
      },
      fail(e) { },
      complete(e) { }
    });
  },  

  // 请求后台记录错误日志
  debug: function (pageName, slug, log) {
    console.log('debug', pageName, slug, log);
    wx.request({
      url: this.globalData.baseUrl + '/errlog',
      data: {
        pageName: pageName,
        slug: slug,
        log: JSON.stringify(log)
      },
      method: 'POST',
      success(res) {
        console.log('debug success', res);
      },
      fail(res) {//wx.request失败
        wx.showToast({
          icon: 'loading',
          title: '网络开小差了, 请稍后再试',
        })
      }
    });
  },

  // 四舍五入
  roundFixed: function (num, fixed) {
    var pos = num.toString().indexOf('.'),
      decimal_places = num.toString().length - pos - 1,
      _int = num * Math.pow(10, decimal_places),
      divisor_1 = Math.pow(10, decimal_places - fixed),
      divisor_2 = Math.pow(10, fixed);
    return Math.round(_int / divisor_1) / divisor_2;
  },

  globalData: {
    appName: '金秋课堂',
    baseUrl: 'https://jinqiu.yunlishuju.com/wxapi',
    imgUrlPrefix: 'https://jinqiu.yunlishuju.com/image/preview',
    //baseUrl: 'http://127.0.0.1:8000/wxapi',
    //imgUrlPrefix: 'http://127.0.0.1:8000/image/preview',
    isLogin: false,   //是否授权并登录
    userInfo: null,   //授权后获取的用户信息, 如昵称头像
    user: null,       //用户信息:userId,nickName,...
    addressInfo: null,//微信通讯地址
  }
})