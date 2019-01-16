var data;
var semesterString;
var showExpired = false;//預設為不顯示已過期會員
var app

var app = new Vue({
    el: '#content',
    data: {
        students: [],
        currentStudent: null,
        semesterString: "",
        payRecord: null,
        newMemberData: null,
    },
    mounted:function(){
        this.semesterString = getSemester();
        getStudentList();
        
    },
    methods:{
        selectStudent: function(student){selectStudent(student)},
        updateMember: updateMember,
        initialPayRecord: initialPayRecord,
        newMember: newMember,
        initialNewMember: initialNewMember,
    }
})

function getStudentList(){
    axios({
        methods: 'get',
        url: 'src/public/index.php/users'
      })
      .then((resp) => {
        app.students = resp.data;
        console.log(resp.data)
      });
}

function getSemester(){
  var date = new Date();
  var year = date.getFullYear() - 1911;//民國年
  var month = date.getMonth() + 1;
  if(month >= 1 || month <= 7) year = year - 1;
  var semester = (month < 8 && month >= 2) ? 2 : 1; //判斷月份是上學期或是下學期
  semesterString = year.toString() + semester.toString();
  return semesterString;
  //document.getElementById("semester").innerHTML = "現在學期：" + semesterString;
}

function selectStudent(student){
    app.currentStudent = JSON.parse(JSON.stringify(student));
}

function updateMember(){
    console.log(app.currentStudent);
    axios.post(
        'src/public/index.php/users/' + app.currentStudent.sid,
        app.currentStudent,
      )
      .then((resp) => {
        //this.students = resp.data;
        getStudentList();
        console.log(resp.data)
      });
}

function initialPayRecord(student){
    var record = new Object();
    record.sid = student.sid;
    record.name = student.name;
    record.department = student.department;
    record.date = new Date().toISOString().split('T')[0];
    record.paid = "";
    record.expired = "";
    app.payRecord = record;
}

function newPayRecord(){
    axios.post(
      'src/public/index.php/pay/' + app.payRecord.sid,
      app.payRecord,
    )
    .then((resp) => {
      //this.students = resp.data;
      getStudentList();
      console.log(resp.data)
    });
}

function initialNewMember(){
    var member = new Object();
    member.sid = "";
    member.name = "";
    member.phone = "";
    member.email = "";
    member.department = "";
    member.joindate = new Date().toISOString().split('T')[0];
    member.totalpay = "";
    member.expire = "";
    app.newMemberData = member;
}

function newMember(){
  axios.post(
    'src/public/index.php/users',
    app.newMemberData,
  )
  .then((resp) => {
    //this.students = resp.data;
    getStudentList();
    console.log(resp.data)
  });
}