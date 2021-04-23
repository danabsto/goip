<?php
/**
 * @copyright Copyright 2021 Undefined.team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\controllers;

use app\models\Line;
use Yii;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * LinesController implements the CRUD actions for Lines model.
 */
class LinesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all Lines models.
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider(['query' => Line::find()]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Updates an existing Lines model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return Response|string
     * @throws UserException
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $simcard = $model->simcard;
        if ($model->load(Yii::$app->request->post()) && $model->save()
            && $simcard->load(Yii::$app->request->post()) && $simcard->save())
            return $this->redirect(["lines/update", "id" => $id]);
        return $this->render('update', [
            'model' => $model,
            'simcard' => $simcard
        ]);
    }

    /**
     * Finds the Lines model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Line the loaded model
     * @throws UserException if the model cannot be found or user dont have access right
     */
    protected function findModel(int $id): Line
    {
        $model = Line::find()
            ->innerJoinWith('device')
            ->where(["device_lines.id" => $id, "user_id" => Yii::$app->user->id])
            ->one();
        if (empty($model))
            throw new UserException('Line not found or you don\'t have access rights to this line');
        return $model;
    }

    /**
     * Deletes an existing Lines model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return Response
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }
}
