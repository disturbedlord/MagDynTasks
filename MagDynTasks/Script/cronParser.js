function cronToShortText(cron) {
  // Split cron expression into parts
  const parts = cron.trim().split(" ");

  if (parts.length !== 5) {
    return "Invalid";
  }

  const [min, hour, day, month, week] = parts;

  // Every minute
  if (cron === "* * * * *") {
    return "Every minute";
  }

  // Hourly
  if (min === "0" && hour === "*") {
    return "Hourly";
  }

  // Daily
  if (
    min === "0" &&
    hour !== "*" &&
    day === "*" &&
    month === "*" &&
    week === "*"
  ) {
    return "Daily";
  }

  // Weekly (based on specific day)
  if (
    min === "0" &&
    hour !== "*" &&
    day === "*" &&
    month === "*" &&
    week !== "*"
  ) {
    // Convert week (0 = Sun, 1 = Mon, ...)

    const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    if (!week.includes("-")) {
      const daysOfWeek = week.split(",");
      const dayNames = daysOfWeek.map((day) => days[parseInt(day)]);
      return "Every " + dayNames.join(", ");
    }
  }

  // Monthly
  if (
    min === "0" &&
    hour !== "*" &&
    day !== "*" &&
    month === "*" &&
    week === "*"
  ) {
    return "Monthly";
  }

  // Yearly
  if (
    min === "0" &&
    hour !== "*" &&
    day !== "*" &&
    month !== "*" &&
    week === "*"
  ) {
    return "Yearly";
  }

  // Every X minutes
  const minMatch = cron.match(/^\*\/(\d+) \* \* \* \*$/);
  if (minMatch) {
    return `Every ${minMatch[1]} min`;
  }

  // Every X hours
  const hourMatch = cron.match(/^0 \*\/(\d+) \* \* \*$/);
  if (hourMatch) {
    return `Every ${hourMatch[1]} hr`;
  }

  // Weekdays (Mon-Fri)
  if (week === "1-5") {
    return "Weekdays";
  }

  // Weekends (Sat-Sun)
  if (week === "0,6") {
    return "Weekends";
  }

  return "Custom";
}

const cronParser = () => {
  const cronSpans = document.querySelectorAll(".cronText");

  cronSpans.forEach((span) => {
    const cron = span.textContent.trim(); // Get the cron value
    const humanReadable = cronToShortText(cron); // Get the human-readable version
    span.textContent = humanReadable; // Set the new value
  });
};
