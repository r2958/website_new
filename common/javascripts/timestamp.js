function currentTimestamp()
{
	// Create a varible with the current date
	var currentTimestamp = new Date();
	var dateString = "";
	
	var currentYear = currentTimestamp.getYear();
	if (currentYear < 2000) currentYear += 1900; 
	dateString += currentYear;
	dateString += "-";
	
	var currentMonth = currentTimestamp.getMonth() + 1;
	if (currentMonth < 10) dateString += "0";
	dateString += currentMonth;
	dateString += "-";
	
	var currentDay = currentTimestamp.getDate();
	if (currentDay < 10) 
	dateString += "0";
	dateString += currentDay
	dateString += " ";
	
	var currentHour = currentTimestamp.getHours()
	if (currentHour < 10) dateString += "0";
	dateString += currentHour;
	dateString += ":";
	
	// To display leading zeros before the minutes
	// Check for minutes less then 10
	var currentMinute = currentTimestamp.getMinutes();
	if (currentMinute < 10) dateString += "0";
	dateString += currentMinute;
	dateString += ":";
	
	// To display leading zeros before the seconds
	// Check for minutes less then 10
	var currentSecond = currentTimestamp.getSeconds();
	if (currentSecond < 10) dateString += 0;
	dateString += currentSecond;
	
	return dateString;
}