# TYPO3 Extension ``md_fullcalendar``
 
This extension brings the Javascript [FullCalendar.io](https://fullcalendar.io/) with switchable views for month, week and day to [ext:calendarize](https://extensions.typo3.org/extension/calendarize/). It is also possible to filter calendars by categories.

In comparison to the Fullcalendar view of ext:calendarize, the shipped plugin of this extension loads the events in the background according to the selected view of the calendar. So you can switch between the month-, week- and day view without reloading the page.

If you use the [Bootstrap](https://getbootstrap.com/) framework in you project, the detail view of an event is opened in a modal window.

## Screenshots

Month view:
![Screenshot month](Documentation/Images/month.png?raw=true "Month view")

Week view:
![Screenshot month](Documentation/Images/week.png?raw=true "Week view")

Day view:
![Screenshot day](Documentation/Images/day.png?raw=true "Day view")

Detail view:
![Screenshot detail](Documentation/Images/detail.png?raw=true "Detail view")

## Requirements

- TYPO3 >= 12.4
- ext:calendarize >= 13.0

## Installation

- Install the extension by using the extension manager or use composer
- Include the static TypoScript of the extension
- Configure the extension by changing variables which are set in `setup.typoscript`
    - `settings.dateFormat`: The format of the dates. Default: `d.m.Y`
    - `settings.timeFormat`: The format of the time. Default: `H:i`
    - `settings.pid.defaultDetailPid`: The Id of the page, where the detail view of an item is shown. On this page you have to insert the plugin `Calendar` of `ext:calendarize` with either `List + Detail` or `Detail only` mode.
    - `settings.showIcsIcalButton`: Flag for showing a button to download the event for inserting it into your own calendar  
    - `persistence.storagePid`: Make sure to set the storagePid to the Pid, where the records of ext:calendarize are stored! Otherwise you can set the `Record Storage Page` individually in the plugin.
- for TYPO3 >= 9 add following routeEnhancer:

```yaml
routeEnhancers:
  PageTypeSuffix:
    type: PageType
    map:
      '/calendar-ajax.html': 1573760945
```

## Usage

- Add the plugin `Fullcalendar for ext:calendarize` on a page
- Switch to the tab `Plugin` to configure some options
    - `Language of calendar`: Select the language for the calendar
    - `Calendar view`: Select the initial view of the calendar (month, week, day)
    - `Parent category for filter`: Select a category of which the child categories are shown in a category filter on the calendar

### Category colors
You can use colors for the different categories in the filters and for the calendar entries.
Just add the following to your stylesheet, whereas the number at the end of `.category123` is the id of the category:
```css
.tx-md-fullcalendar .category123 {
    color: #fff;
    background-color: red;
}
```

### Template layouts

You are able to configure template layouts in TsConfig:

```
tx_mdfullcalendar_cal {
  templateLayouts {
    1 = First layout
    2 = Second layout
  }
}
```

## Bugs and Known Issues
If you find a bug, it would be nice if you add an issue on [Github](https://github.com/cdaecke/md_fullcalendar/issues).

# THANKS

Thanks a lot to all who make this outstanding TYPO3 project possible!

## Credits

- Thanks to Tim Lochmüller who has developed the extension [Calendarize](https://extensions.typo3.org/extension/calendarize/), which is the base for this extension.
- Thanks to Adam Shaw, the creator of [FullCalendar](https://fullcalendar.io/), which I use as calendar.
- The extension icon is based on the logo of FullCalendar.
