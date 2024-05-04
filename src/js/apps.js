$.getJSON("../server/?db-info",
function (data, textStatus, jqXHR) {
    $(".app_title").html(data);
}
).fail(e=>{
console.log(e);
alert("Application non configurée!!")
});

let APP=""
$("#loading").hide();
db.set('version','Beta')
function lacrea_load(destination="#app",file="apps/home/"){
    $("html").scrollTop(0);
    $("#loading").show();
    db.set(destination,file)
    console.log("load "+file);
    // Load app section
    $(destination).html("<div style='height:100vh; display:grid;align-items:center;justify-content:center'>Chargment....</div>");
    $.get(file+"?"+db.get('version'),
        function (data, textStatus, jqXHR) {
            $(destination).html(data);
        },
        ""
    ).fail((e)=>{
        $(destination).html(e);
    }).always(e=>{
        $("#loading").hide();
    })
    // Load popup section
    $.get(file+"popup.html"+"?"+db.get('version'),
        function (data, textStatus, jqXHR) {
            $('#popup>.pop').html(data);
        },
        ""
    )
}

function popup(section=null,id=null,table=null) {
    if (section==null) {
        $('#popup').addClass('hidden');
        $('#popup>.pop>*').addClass('hidden');
        return
    }
    $('#popup').removeClass('hidden');
    $('#popup>.pop>'+section).removeClass('hidden');
    if (id!=null && table!=null) {
        db.set('id_edit',id)
        $(".edit_").prepend("<span id='infos_edit' class='text-center'>LOADING....</span>");
        $.get(`../server/?${table}-byId=${id}`,
            function (data, textStatus, jqXHR) {
                data=data.data
                if (data.length==1) {
                    data=data[0]
                    keys=Object.keys(data)
                    keys.forEach(key => {
                        $(`.edit_>*>*[name='${key}']`).val(data[key]);
                    }); 
                }else{
                    alert("Erreur dans la reponse!")
                    console.log(data);
                }
            },
            "json"
        ).always(e=>{
            $("#infos_edit").addClass('hidden');
        });    
    }
    
}
function formToDic(form) {
    let dict={}
    for(var i of form.entries()){
        dict[i[0]]=i[1];
    }
    return dict
}

// $('.popup>.edit_').submit(function (e) { 
//     e.preventDefault();
//     let form = new FormData(this)
//     form.get()
//     $.ajax({
//         type: 'POST', //         url: '../server/?$table-update='+form.get("id"),
//         data: form,
//         contentType: false,
//         processData: false,
//         success: function (response) {
//             alert('$table Updated avec succes!')
//             getData()
//         },fail:function (er) { 
//             alert('Erreur : $table non updated')
//         }
//     });
// });


// Loading Menu bar
// $("#bar").html(`
//     <div class="text-xl text-white h-16 p-4 bg-black">Tables</div>
//     <button class="text-left p-4 bg-green-500 mx-1 rounded hover:bg-white focus:bg-white ">Home</button>
//     <div class="mb-8"></div>
// `);