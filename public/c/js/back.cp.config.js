var toast = typeof(auiToast) == 'function' ? new auiToast({}) : '' ; //初始化提示工具
var dialog = typeof(auiDialog) == 'function' ? new auiDialog({}) : ''; //初始化对话框工具
var GB_VAR = [];
var cBaseDomain = '/'; //接口域名
var cConfig = {
  baseDomain    : cBaseDomain ,
  aMapKey       : ' ', //高德地图API之KEY。
  avatarPath   : '' , //头像公共路徑
  defaultAvatar : cBaseDomain + 'c/images/avatar.png', //默认头像
}
var cApiUrl = {
  easemobToken  : " ", //环信token
  aMapScript    : 'http://webapi.amap.com/maps?v=1.4.0&key='+cConfig.aMapKey, //高德地图api
  uploadAvatar  : ' ',   // 传头像API
  getUserInfo   : cConfig.baseDomain + 'carpool/user/get_user', //取得用户信息
  getUserStatis : cConfig.baseDomain + 'carpool/user/get_user_statis', //取得用户拼车统计数据
  getInfoLists  : cConfig.baseDomain + 'carpool/info/get_lists', //取得需求列表
  getWallLists  : cConfig.baseDomain + 'carpool/wall/get_lists', //取空座位求列表
  getAddress    : cConfig.baseDomain + 'carpool/publics/get_address', //取得地址列表
  getMyAddress  : cConfig.baseDomain + 'carpool/address/get_myaddress', //取得我的地址
  addInfo       : cConfig.baseDomain + 'carpool/info/add', //发布需求
  addWall       : cConfig.baseDomain + 'carpool/wall/add', //发布空座位
  getOfentTrips : cConfig.baseDomain + 'carpool/myroute/get_ofent_trips', //取得常用行程
  getMyroute    : cConfig.baseDomain + 'carpool/myroute/myroute', //取得我的行程列表
  cancelRoute   : cConfig.baseDomain + 'carpool/myroute/cancel_route', //取消行程
  finishRoute   : cConfig.baseDomain + 'carpool/myroute/finish_route', //完结行程
  likeRoute     : cConfig.baseDomain + 'carpool/wall/like', //点赞行程
  acceptDemand  : cConfig.baseDomain + 'carpool/info/accept_demand', //接受需求
  riding        : cConfig.baseDomain + 'carpool/info/riding', //乘车
  getInfoView   : cConfig.baseDomain + 'carpool/info/get_view', // 取得空座位详细信
  getWallView   : cConfig.baseDomain + 'carpool/wall/get_view', // 取得空座位详细信息
  getWallViewPassengers: cConfig.baseDomain + 'carpool/info/get_passengers', // 取得空座位乘客
  editProfile   : cConfig.baseDomain + 'carpool/user/change_profile', // 修改个人资料
  editProfileAdress: cConfig.baseDomain + 'carpool/user/change_address', // 修改个人资料的公司和家地址
  checkLogin    : cConfig.baseDomain + 'carpool/index/check_login', // 验证登入状态
  login         : cConfig.baseDomain + 'carpool/publics/login', // 登入
  logout        : cConfig.baseDomain + 'carpool/publics/logout', // 登出
  createAddress : cConfig.baseDomain + 'carpool/address/add', // 创建站点
  wallComments   : cConfig.baseDomain + 'carpool/comment/wall', //取得空座位评论列表

}
