<?php
namespace app\forms;

use facade\Json;
use std, gui, framework, app;

class MainForm extends AbstractForm
{
    //Dedicated to 2977 people,
    function refresh($period)
    {
        $this->dated->text = new Time($GLOBALS['weather']->getUTCTime($period) * 1000)->toString("MM/dd/yyyy hh:mm a");
        $this->temp->text = $GLOBALS['weather']->getTemperature($period);
        $this->pressure->text = $GLOBALS['weather']->getPressure($period);
        $this->humidity->text = $GLOBALS['weather']->getHumidity($period);
        $this->winddirection->rotate = $GLOBALS['weather']->getWindDegree($period);
        $this->windspeed->text = $GLOBALS['weather']->getWindSpeed($period);
        $this->main->text = $GLOBALS['weather']->getMain($period);
        $this->icon->image = UXImage::ofUrl($GLOBALS['weather']->getDefIcon($period));
        $desc = "A ".$GLOBALS['weather']->getDescription($period)." at ".$GLOBALS['weather']->getLocation($period)." is forecasted on ".$this->dated->text.". ";
        $desc .= "The temperature will be between ".$GLOBALS['weather']->getMinTemp($period)." and ".$GLOBALS['weather']->getMaxTemp($period).", and it feels like it's ".$GLOBALS['weather']->getFeelsLike($period).". ";
        $desc .= "The pressure is presumed to be ".$GLOBALS['weather']->getPressure($period)." and humidity of ".$GLOBALS['weather']->getHumidity($period).". ";
        $desc .= "The wind speed may be like ".$GLOBALS['weather']->getWindSpeed($period)." and it is ".$GLOBALS['weather']->getWindDegree($period)."°. ";
        if ($GLOBALS['weather']->isRaining($period)) {
            $desc .= "It's raining today, the volume of rain is ".$GLOBALS['weather']->getRainVolume($period)." so don't forget to take an umbrella ;). ";
        } if ($GLOBALS['weather']->isSnowing($period)) {
            $desc .= "Ooh :O Snowy! That's like ".$GLOBALS['weather']->getSnowVolume($period)." of snow! ";
        } $desc .= "There will be ".$GLOBALS['weather']->getClouds($period)." of sky covered with clouds. ";
        $desc .= "Sunrise happens to be on ".new Time($GLOBALS['weather']->getLocalSunrise($period) * 1000)->toString("hh:mm a")." while ";
        $desc .= "sunset will occur on ".new Time($GLOBALS['weather']->getLocalSunset($period) * 1000)->toString("hh:mm a").". ";
        $this->desc->text = $desc;
    }
    
    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $e = null)
    {    
        $location = Json::fromFile("http://ip-api.com/json/?fields=lat,lon");
        $GLOBALS['weather'] = new yaWeather("XXX"); //TYPE YOUR API KEY HERE!!!
        $GLOBALS['weather']->setUnits($this->config->get("Units"));
        $GLOBALS['weather']->getByCoordinates($location['lat'], $location['lon']);
        call_user_func([$this, "refresh"], 0);
    }

    /**
     * @event next.action 
     */
    function doNextAction(UXEvent $e = null)
    {    
        if ($GLOBALS['time'] < 39) { call_user_func([$this, "refresh"], ++$GLOBALS['time']); } else { $GLOBALS['time'] = 0; call_user_func([$this, "refresh"], $GLOBALS['time']); }
    }

    /**
     * @event prev.action 
     */
    function doPrevAction(UXEvent $e = null)
    {    
        if ($GLOBALS['time'] > 0) { call_user_func([$this, "refresh"], --$GLOBALS['time']); } else { $GLOBALS['time'] = 39; call_user_func([$this, "refresh"], $GLOBALS['time']); }
    }

    /**
     * @event search.action 
     */
    function doSearchAction(UXEvent $e = null)
    {    
        $str = $this->searchbar->text;
        $str = str::split($str, ", ");
        $GLOBALS['weather']->getByCity($str[0], $str[1]);
        if ($GLOBALS['weather']->getCode() !== "404") call_user_func([$this, "refresh"], 0);
        else { $this->main->text = "404"; $this->desc->text = "The City, Country you've typed is not found!"; }
    }

    /**
     * @event suckit.action 
     */
    function doSuckitAction(UXEvent $e = null)
    {
        $this->config->set("Units", "SI"); $GLOBALS['weather']->setUnits($this->config->get("Units"));
    }

    /**
     * @event metric.action 
     */
    function doMetricAction(UXEvent $e = null)
    {
        $this->config->set("Units", "Metric"); $GLOBALS['weather']->setUnits($this->config->get("Units"));
    }

    /**
     * @event Imperial.action 
     */
    function doImperialAction(UXEvent $e = null)
    {
        $this->config->set("Units", "Imperial"); $GLOBALS['weather']->setUnits($this->config->get("Units"));
    }

    /**
     * @event reload.action 
     */
    function doReloadAction(UXEvent $e = null)
    {    
        call_user_func([$this, "refresh"], 0);
    }
    //Never forget.
}
