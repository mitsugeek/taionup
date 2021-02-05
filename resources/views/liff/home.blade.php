<!DOCTYPE html>
<html lang="ja" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>体温投稿管理システム</title>
    <meta property="og:title" content="{{env('APP_NAME')}}">
    <meta property="og:type" content="website">
    <meta property="og:description" content="体温を投稿する為の管理システム">
    <meta property="og:url" content="{{env('APP_URL')}}">
    <meta property="og:site_name" content="{{env('APP_NAME')}}">
    <meta property="og:image" content="{{env('LOGO_URL')}}">
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
</head>
<style>
    body {
        width:100%;
        word-break: break-all;
    }
</style>
<body>
<div id="app">
  <div v-if="displayName">
    @{{displayName}} さんようこそ。
  </div>
  
  <hr />

  <button v-if="!isLogin" v-on:click="login">ログイン</button>
  <button v-if="isLogin" v-on:click="logout">ログアウト</button>

  <hr />

  <button v-on:click="getUser">ユーザ情報取得(サーバサイド)</button>
  <button v-on:click="getSession">セッション情報取得(サーバサイド)</button>
  
  <hr />

  <div>
    @{{userInfo.userId}}
  </div>
  <div>
    @{{userInfo.displayName}}
  </div>

  <div>
    <img :src="userInfo.pictureUrl" style="width:100px;">
  </div>

  <div>
    @{{userInfo.statusMessage}}
  </div>
</div>
<script>
var app = new Vue({
  el: '#app',
  data: {
    message: 'メニュー',
    isLogin:true,
    displayName:"",
    iDToken:"",
    userInfo:{}
  },
  created: function(){
    let _this = this;
    liff.init({ liffId: "{{env('LINE_LIFF_ID')}}" })
    .then(() => {
        _this.initializeApp();
    })
  },
  methods:{
    initializeApp:function(){
      if (liff.isLoggedIn()) {
        //ログイン中の場合
        this.isLogin = true;
      } else {
        this.isLogin = false;
      }

      liff.getProfile()
      .then(profile => {
        this.displayName = profile.displayName
      })
      .catch((err) => {
        console.log('error', err);
      });

      this.iDToken = liff.getIDToken();
    },
    login: function (event) {
        console.log(event);
        liff.login();
        this.isLogin = true;
    },

    logout:function(event){
        console.log(event);
        liff.logout();
        this.isLogin = false;
    },

    getUser:function(event){
      let _this = this;
      let accessToken = liff.getAccessToken();
      axios.post("{{route('getUser')}}", { token: accessToken } ,{
        headers:{
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type':'application / x-www-form-urlencoded'
          },
        withCredentials:true,
      })
      .then(function(res){
        console.log("then");
        console.log(res);
        _this.message = res.data;
        _this.userInfo = res.data;
        _this.pictureUrl = res.data.pictureUrl;
      })
      .catch(function(res){
        console.log("error");
        console.log(res)
      })
    },

    getSession:function(event){
      let _this = this;
      axios.get("{{route('getSessionUser')}}",{withCredentials: true})
      .then(function(res){
        _this.message = res.data;
        _this.userInfo = res.data;
        _this.pictureUrl = res.data.pictureUrl;
      });
    }
  }
});
</script>
</body>
</html>