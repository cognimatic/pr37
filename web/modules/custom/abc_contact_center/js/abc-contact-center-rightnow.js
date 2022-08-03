RightNow.Client.Controller.addComponent({
    container_element_id: "myChatLinkContainer",
    info_element_id: "myChatLinkInfo",
    link_element_id: "myChatLink",
    p: "696",
    open_in_new_window: false,
    instance_id: "sccl_0",
    module: "ConditionalChatLink",
    type: 7
  },
  "https://argyllandbute.widget.custhelp.com/ci/ws/get"
);

window.onload = changeChatPic();

function changeChatPic() {
  var linktext = document.getElementById("myChatLinkInfo");
  if (linktext.innerHTML = "Agents are available with no wait time.") {
    document.getElementById("myChatLinkInfo").innerHTML = "<img src='/sites/default/files/online_help_green.gif'  alt='request help via chat' />";
  };
};

var p = document.getElementById('myChatLinkContainer');
p.innerHTML = p.innerHTML.replace(/&nbsp;/gi, '');
