// JScript source code
function myCalendar(sInsName)
{

	this.insName = sInsName;
	this.outputDiv = null;
	this.day = null;
	this.month = null;
	this.year = null;
	this.OnChange = null;
	this.OnClear = null;

	function Init(oDivOutput, sDay, sMonth, sYear, nStartYear)
	{
		var date = new Date();
		this.day = sDay	 ? sDay		: date.getDate();
		this.month = sMonth ? sMonth	: date.getMonth();
		this.year = sYear	 ? sYear	: date.getYear();
		this.weekDays = new Array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa");
		this.monthNames = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		this.outputDiv = oDivOutput;
		this.outputDiv.style.display = "none";
		this.CreateSceleton(nStartYear);
		this.CreateDayTable();
	}

	function Show(nTop, nLeft)
	{
		this.outputDiv.style.posTop = nTop;
		this.outputDiv.style.posLeft = nLeft;
		this.outputDiv.style.display = "block";
	}

	function Hide()
	{
		this.outputDiv.style.display = "none";
	}

	function Clear() {
		if (this.OnClear != null) {
			this.OnClear();
		}
		this.Hide();
	}

	function View(sDay, sMonth, sYear)
	{
		if (sYear.length == 2)
			sYear = "20" + sYear;

		sDay = Math.ceil(sDay);
		sMonth = Math.ceil(sMonth);
		sYear = Math.ceil(sYear);

		if (this.day == sDay && this.month == sMonth && this.year == sYear)
			return;

		this.day = sDay;
		this.month = sMonth;
		this.year = sYear;

		document.getElementById(this.insName + "_fm").value = this.month;
		document.getElementById(this.insName + "_fy").value = this.year;

		this.CreateDayTable();
		if (this.OnChange != null)
			this.OnChange(this);
	}

	function CreateSceleton(nStartYear)
	{
		var date = new Date();
		if (nStartYear == null) {
			nStartYear = date.getFullYear() - 5;
		}

		

		var html = "<table cellpadding=0 cellspacing=1 border=0>";
		html += "<tr>";
		html += "	<td valign=top>";
		html += "		<select name=month onchange=\"" + this.insName + ".View(" + this.day + ", document.getElementById('" + this.insName + "_fm').value, document.getElementById('" + this.insName + "_fy').value)\" id=\"" + this.insName + "_fm\" class=\"mc-select-c\">";
		for (var i = 0; i < 12; i++)
			html += "			<option value=" + i + (this.month == i?" selected":"") + ">" + this.monthNames[i] + "</option>";
		html += "		</select>";
		html += "	</td>";
		html += "	<td valign=top>";
		html += "		<select name=year onchange=\"" + this.insName + ".View(" + this.day + ", document.getElementById('" + this.insName + "_fm').value, document.getElementById('" + this.insName + "_fy').value)\" id=\"" + this.insName + "_fy\" class=\"mc-select-c\">";
		for (var i = nEndYear; i >= nStartYear; i--)
			html += "			<option value=" + i + (this.year == i?" selected":"") + ">" + i + "</option>";
		html += "		</select>";
		html += "	</td>";
		html += "	<td valign=top><button style=\"height:19;\" onclick=\"" + this.insName + ".Clear()\">C</button></td>";
		html += "	<td valign=top><button style=\"height:19;\" onclick=\"" + this.insName + ".Hide()\">X</button></td>";
		html += "</tr>";
		html += "<tr>";
		html += "	<td colspan=4>";
		html += "		<span id=" + this.insName + "_dc>";
		html += "		</span>";
		html += "	</td>";
		html += "</tr>";
		html += "</table>";
		this.outputDiv.innerHTML = html;
		this.daytable = document.getElementById(this.insName + "_dc");
	}

	function CreateDayTable()
	{
		var tdWidth = 21;
		var tdHeight = 15;
		var html = "<table cellpadding=0 cellspacing=1 border=0 class=\"mc-table-c\">";
		html += "<tr>";
		for (var i = 0; i < 7; i++)
			html += "	<th width=\"" + tdWidth + "\" class=\"mc-th-c mc-td\">" + this.weekDays[i] + "</th>";
		html += "</tr>";
		html += "<tr height=\"" + tdHeight + "\">";
		var date = new Date(this.year, this.month, 1, 0, 0, 0);
		var day = date.getDay();
		var monthDay, weekDay;
		weekDay = day == 6 ? 7 : day + 1;
		if (weekDay)
			while (--weekDay)
				html += "<td class=\"mc-td-cn\"> </td>";
		var flagStop = false;
		var j = 0;
		while (!flagStop) {
			for (var i = 0; !flagStop && i < 7; i++)
			{
				monthDay = j * 7 + i;
				weekDay = (monthDay + day + 6) % 7;
				date.setMonth(this.month);
				date.setDate(monthDay);
				if (monthDay == date.getDate())
				{
					if (weekDay == 0 && monthDay != 1)
						html += "</tr><tr height=\"" + tdHeight + "\">";
					html += "<td ";
					if (monthDay == this.day)
						html += "class=\"mc-td-cd mc-td\"";
					else
						if (weekDay == 0)
							html += "class=\"mc-td-ch mc-td\"";
						else
							html += "class=\"mc-td-cu mc-td\"";
					html += " onclick=\"" + this.insName + ".View(" + monthDay + ", " + this.month + ", " + this.year + ");" + this.insName + ".OnClick();\">" + monthDay + "</td>";
				}
				else
					if (monthDay > 27)
						flagStop = true;
			}
			j++;
		}
		if (weekDay)
			while (weekDay++ < 7)
				html += "<td class=\"mc-td-cn\"> </td>";
		html += "</tr>";
		html += "</table>";
		this.daytable.innerHTML = html;
	}
	function OnClick()
	{
		if (this.OnSelect != null)
			this.OnSelect(this.day, Math.ceil(this.month) + 1, this.year);
	}
	this.OnClick = OnClick;
	this.Init = Init;
	this.View = View;
	this.Show = Show;
	this.Clear = Clear;
	this.Hide = Hide;
	this.CreateSceleton = CreateSceleton;
	this.CreateDayTable = CreateDayTable;
}